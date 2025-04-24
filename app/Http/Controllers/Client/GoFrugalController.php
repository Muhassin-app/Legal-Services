<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\GoFrugal;
use App\Jobs\GoFrugalSync;
use App\Models\Category;
use App\Models\Category_translation;
use App\Models\CategoryHistory;
use App\Models\Client;
use App\Models\ClientLanguage;
use App\Models\Country;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\Type;
use App\Models\User;
use App\Models\UserAddress;
use App\Models\Vendor;
use App\Models\VendorMedia;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class GoFrugalController extends BaseController
{
    use GoFrugal;
    
    private $_cacheTime = 60; //in seconds

    private $folderName = 'prods';

    private function checkCachedData($key, $function){
        if(Cache::has($key)){
            return Cache::get($key);
        }
        return Cache::remember($key, $this->_cacheTime, function () use ($function) {
            return call_user_func(array($this, $function));
        });
    }

    private function generateBarcodeNumber()
	{
		$random_string = substr(md5(microtime()), 0, 14);
		while (ProductVariant::where('barcode', $random_string)->exists()) {
			$random_string = substr(md5(microtime()), 0, 14);
		}
		return $random_string;
	}

    public function index(Request $request)
    {
        if(!Session::has('job_running')){
            dispatch(new GoFrugalSync())->onQueue('go_frugal');
            return redirect()->back()->with('success', 'Data is being Synced');
        }
        return redirect()->back()->with('success', 'Data is already being synced');
    }

    public function syncData(){
        $this->fetchCategories();
        $this->fetchCustomers();
        $this->fetchProducts();
    }

    protected function fetchProducts(){
        $response = $this->checkCachedData('gofurgal_products', 'getProducts');

        if (!$response['status']) {
            return redirect()->back()->withErrors(['error' => $response['message']]);
        }
        
        $products = $response['data'];
        $client_lang = ClientLanguage::where('is_primary', 1)->first();
        if (!$client_lang) {
            $client_lang = ClientLanguage::where('is_active', 1)->first();
        }

        try{
            foreach($products->items as $product){
                DB::beginTransaction();
                $stock = $product->stock[0] ?? [];
                $supplier = $stock->supplierName ?? '';
                // $vendor = Vendor::where('name', 'like', "%$supplier%")->first();
                $vendor = Vendor::first();

                $filteredData = array_filter((array) $stock, function ($item, $key) {
                    return strpos($key, 'Cat') === 0 && !empty($item);
                }, ARRAY_FILTER_USE_BOTH);

                if(count($filteredData)){
                    $id = current($filteredData);
                    $category = Category::where('slug', 'like', "%$id%")->first();
                }
                $type = Type::where('title', 'like', "%$product->fulfilmentMode%")->first();

                //adding product
                $item = [
                    'sku' => $stock->itemReferenceCode,
                    'title' => $product->itemName,
                    'url_slug' => '',
                    'description' => $product->description,
                    'body_html' => $product->detailedDescription,
                    'vendor_id' => $vendor->id ?? 1,
                    'category_id' => $category->id ?? '',
                    'type_id' => $type->id ?? 1,
                    'is_new' => 1,
                    'is_live' => 1,
                    'weight' => $product->weight,
                    'has_variant' => 1,
                    'publish_at' => date('Y-m-d', $stock->itemTimeStamp),
                    'created_at' => date('Y-m-d', $stock->itemTimeStamp),
                    'returnable' => $product->isCancellable == 'Y' ? 1 : 0,
                    'return_days' => $product->returnPeriod
                ];

                $item = Product::updateOrCreate([
                    'sku' => $stock->itemReferenceCode
                ],$item);

                if(empty($item))
                    DB::rollBack();

                //adding product translation
                $productTranslation = [
                    'title' => $product->itemName,
                    'body_html' => $product->detailedDescription,
                    'meta_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'product_id' => $item->id,
                    'language_id' => $client_lang->language_id
                ];

                $productTranslation = ProductTranslation::updateOrCreate([
                    'product_id' => $item->id,
                    'language_id' => $client_lang->language_id
                ], $productTranslation);

                if(empty($productTranslation))
                    DB::rollBack();

                //adding product category
                $productCategory = [
                    'product_id' => $item->id,
                    'category_id' => $category->id ?? ''
                ];

                $productCategory = ProductCategory::updateOrCreate($productCategory, $productCategory);

                if(empty($productCategory))
                    DB::rollBack();

                //adding product variant
                $variant = [
                    'sku' => $stock->itemReferenceCode,
                    'product_id' => $item->id,
                    'title' => $product->itemName,
                    'quantity' => $stock->stock,
                    'price' => $stock->originalPrice,
                    'status' => 1,
                    'barcode' => $this->generateBarcodeNumber(),
                    'compare_at_price' => $stock->mrp,
                    'cost_price' => $stock->costPriceWithoutTax,
                    'created_at' => date('Y-m-d', $stock->itemTimeStamp),
                    'updated_at' => date('Y-m-d', $stock->itemTimeStamp)
                ];
                
                $variant = ProductVariant::updateOrCreate([
                    'sku' => $stock->itemReferenceCode
                ],$variant);

                if(empty($variant))
                    DB::rollBack();

                if(!empty($product->imageUrl)){
                    //adding vendor media
                    $media = [
                        'media_type' => 1,
                        'vendor_id' => $vendor->id,
                        'path' => $product->imageUrl,
                        'created_at' => date('Y-m-d', $stock->itemTimeStamp),
                        'updated_at' => date('Y-m-d', $stock->itemTimeStamp)
                    ];

                    $media = VendorMedia::updateOrCreate([
                        'vendor_id' => $vendor->id,
                        'media_type' => 1
                    ], $media);

                    if(empty($media))
                        DB::rollBack();

                    //product image
                    $image = [
                        'product_id' => $item->id,
                        'media_id' => $media->id,
                        'is_default' =>  1,
                        'created_at' => date('Y-m-d', $stock->itemTimeStamp),
                        'updated_at' => date('Y-m-d', $stock->itemTimeStamp)
                    ];
                    
                    $image = ProductImage::updateOrCreate([
                        'product_id' => $item->id,
                        'media_id' => $media->id,
                    ], $image);

                    if(empty($image))
                        DB::rollBack();
                }
                DB::commit();
            }
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
        return response()->json(['message' => 'Products Added Successfully'], 200);
    }

    protected function fetchCustomers(){
        try{
            $customers = $this->checkCachedData('gofurgal_customers', 'getCustomers');
            if (!$customers['status']) {
                return redirect()->back()->withErrors(['error' => $customers['message']]);
            }
            foreach($customers['data']->eCustomers as $customer){
                $country = Country::where('nicename', 'like', '%' . $customer->country. '%')->first();
                $user = [
                    'name' => $customer->name,
                    'email' => $customer->email,
                    'country_id' => $country->id ?? 1,
                    'phone_number' => $customer->mobile,
                    'is_superadmin' => 0,
                    'created_at' => date('Y-m-d', $customer->syncTS),
                    'updated_at' => date('Y-m-d', $customer->syncTS)
                ];
                $user = User::updateOrCreate([
                    'email' => $customer->email
                ],$user); 
                $address = [
                    'user_id' => $user->id,
                    'address' => $customer->address1,
                    'city' => $customer->city,
                    'state' => $customer->state,
                    'country' => $customer->country,
                    'pincode' => $customer->pincode,
                    'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude,
                    'created_at' => date('Y-m-d', $customer->syncTS),
                    'updated_at' => date('Y-m-d', $customer->syncTS)
                ];
                UserAddress::updateOrCreate([
                    'user_id' => $user->id,
                    'latitude' => $customer->latitude,
                    'longitude' => $customer->longitude
                ],
                $address
                );
            }
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
    
        return response()->json(['message' => 'Customer Added Successfully'], 200);
    }

    //fetching categories

    protected function fetchCategories()
    {
        try{
            $categories = $this->checkCachedData('gofurgal_categories', 'getCategory');
            if (!$categories['status']) {
                return redirect()->back()->withErrors(['error' => $categories['message']]);
            }
            $user = Auth::user();
            $code = $user->code ?? '245bae';
            foreach ($categories['data']->categories as $category) {
                $newCategory = [
                    'slug' => $category->displayName,
                    'created_at' => date('Y-m-d', $category->timeStamp),
                    'updated_at' => date('Y-m-d', $category->timeStamp),
                    'status' => $category->Status == 'Y' ? 1 : 0,
                    'client_code' => $code,
                    'position' => 1,
                    'parent_id' => 1,
                    'is_visible' => 1,
                    'type_id' => 6 //for subcategory
                ];

                $newCategory = Category::firstorCreate(['slug' => $category->displayName]);
                Category_translation::updateOrCreate(
                    [
                        'category_id' => $newCategory->id,
                        'language_id' => 1
                    ],
                    [
                        'category_id' => $newCategory->id,
                        'language_id' => 1,
                        'name' => $newCategory->slug,
                        'meta_title' => '',
                        'meta_description' => '',
                        'meta_keywords' => ''
                    ]
                );
                $this->addCategoryHistory($newCategory, $user);
                $this->addCategoryTranslation($newCategory);
                if (!empty($category->categoryValues)) {
                    foreach ($category->categoryValues as $subCategory) {
                        $newSubCategory = [
                            'slug' => $subCategory->categoryValueName,
                            'status' => $subCategory->catStatus == 'Y' ? 1 : 0,
                            'created_at' => date('Y-m-d', $subCategory->syncTs),
                            'updated_at' => date('Y-m-d', $subCategory->syncTs),
                            'parent_id' => $newCategory->id,
                            'client_code' => $code,
                            'is_visible' => 1,
                            'type_id' => 1 //for product
                        ];
                        $newSubCategory = Category::updateOrCreate(['slug' => $subCategory->categoryValueName], $newSubCategory);
                        $this->addCategoryHistory($newSubCategory, $user);
                        $this->addCategoryTranslation($newSubCategory);
                    }
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            \Log::error($e->getMessage());
            return redirect()->back()->withInput()->withError($e->getMessage());
        }
        return response()->json(['message' => 'Categories Added Successfully'], 200);
    }

    private function addCategoryHistory($newCategory, $user)
    {
        $hs = new CategoryHistory();
        $hs->category_id = $newCategory->id;
        $hs->action = 'Add';
        $hs->updater_role = 'Admin';
        $hs->update_id = $user->id ?? 1;
        $hs->client_code = $user->code ?? '245bae';
        $hs->save();
    }

    private function addCategoryTranslation($category)
    {
        Category_translation::updateOrCreate(
            [
                'category_id' => $category->id,
                'language_id' => 1
            ],
            [
                'category_id' => $category->id,
                'language_id' => 1,
                'name' => $category->slug,
                'meta_title' => '',
                'meta_description' => '',
                'meta_keywords' => ''
            ]
        );
    }
    
    //fetching categories end

    //fetching all vendors
    private function fetchAllVendors()
    {
        $response = $this->checkCachedData('gofurgal_vendors', 'getVendors');
        if (!$response['status']) {
            return redirect()->back()->withErrors(['error' => $response['message']]);
        }
        $vendors = $response['data'];
        foreach ($vendors->supplierMaster as $key => $vendor) {
            $newVendor = new Vendor();
            $newVendor->name = $vendor['name'];
            $newVendor->slug = strtolower(str_replace(' ', '-', $newVendor->name));
            $newVendor->address = $vendor['address1'];
            $newVendor->city = $vendor['address2'];
            $newVendor->state = $vendor['address3'];
            $newVendor->email = $vendor['emailId'];
            $newVendor->phone_no = $vendor['mobileNumber'];
            $newVendor->pincode = $vendor['pincode'];
            $newVendor->save();
        }
        return response()->json(['message' => 'Vendor Added Successfully'], 200);
    }

    //fetching vendors end
}
