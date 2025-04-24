<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\Category;
use App\Models\ClientLanguage;
use App\Models\ClientPreference;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\Validator;
use App\Http\Traits\ToasterResponser;
use Illuminate\Support\Str;
use App\Models\Client;
use App\Models\ProductAttribute;
use App\Models\ProductImage;
use App\Models\UserVendor;
use App\Models\{Vendor, ProductAvailability, ServiceArea, Type, User, VendorCategory};
use App\Models\VendorMedia;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Session;
use Carbon\Carbon;

class PostController extends FrontController
{

    use ToasterResponser;

    private $folderName = 'prods';
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $navCategories = $this->categoryNav($langId);
        $celebrity_check = ClientPreference::first()->value('celebrity_check');

        $categories = Category::with('translation_one','type')->where('id', '>', '1');
        \Log::info(@$getAdditionalPreference['is_rental_weekly_monthly_price']);
        if(@$getAdditionalPreference['is_rental_weekly_monthly_price']==1){
            $categories->whereHas('type', function($q){
                $q->where('service_type', 'rental_service');
                $q->orWhere('service_type', 'p2p');
            });
        }else{
            $categories->whereHas('type', function($q){
                $q->where('service_type', 'p2p');
            });
        }
        
        $categories = $categories->where('is_core', 1)->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1);

        if ($celebrity_check == 0)
            $categories = $categories->where('type_id', '!=', 5);   # if celebrity mod off .

        $categories = $categories->get();
        $serviceaArea = ServiceArea::get();

       
        return view('frontend.template_nine.posts.add_post_rental')->with(['categories' => $categories, 'navCategories' => $navCategories, 'serviceaArea' => $serviceaArea]);
    }



    public function getCategoryAttributes(Request $request)
    {
        $category_id = $request->category_id;
        $productAttributes = [];
        if( checkTableExists('attributes') ) {
            $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                ->select('attributes.*')
                ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                ->where('attribute_categories.category_id', $category_id)
                ->where('attributes.status', '!=', 2)
                ->orderBy('position', 'asc')->get();
            
          
        }

        $returnHTML = view('frontend.template_nine.posts.product-attribute')->with(['productAttributes' => $productAttributes,  'attribute_value' => [], 'attribute_key_value' =>[]])->render();

        // dd($returnHTML);

        return response()->json(array('success' => true, 'html'=>$returnHTML));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            // dd($request->all());
            

            //ProductVariant::where('product_id',$id)->update(['status'=>0]);
            // $product = Product::where('id', $id)->firstOrFail();
            $rule = array(
                'product_name' => 'required|string',
                'category_id' => 'required',
                'daily_price' => 'required',
                'week_price' => 'required',
                'monthly_price' => 'required',
                'images.*' => 'required',
                'product_description' =>  'required',
                'date_availability' => 'required'
                // 'minimum_order_count' => 'required|numeric|min:1',
                // 'batch_count' => 'required|numeric|min:1'
            );
            // dd($request->all());
            $validation  = Validator::make($request->all(), $rule);
            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation);
            }
           

            $product = $this->saveProduct($request);
            $fileIds = $this->uploadProductImages($product, $request);

            

            if( clientPrefrenceModuleStatus('p2p_check') ) {
                    // Add Attributes
                    $this->addProductAttribute($request, $product);
            }

            $vendor = Vendor::where('id', $product->vendor_id)->first();

            if(@$vendor->slug && @ $product->url_slug){
                return redirect()->route('productDetail', [
                    'vendor' => $vendor->slug,
                    'id' => $product->url_slug
                ]);
            }
            
            
           
            $toaster = $this->successToaster(__('Success'),__('Product updated successfully') );
            return redirect()->back()->with('toaster', $toaster);
        } catch (\Exception $e) {
        //    dd($e->getMessage());

            $toaster = $this->errorToaster(__('ERROR'),$e->getMessage() );
            return redirect()->back()->with('toaster', $toaster);

        }
    }


    function addProductWithAttribute(Request $request) {
		try {
			$validator = Validator::make($request->all(), [
				// 'sku' => 'required|unique:products',
				// 'url_slug' => 'required|unique:products',
				'category_id' => 'required',
				'product_name' => 'required',
				// 'vendor_id'	=>	'required'
			]);

			if ($validator->fails()) {
			
				return $this->errorResponse($validator->errors()->first(), 422);
			}
            // dd($request->all());
			$client = Client::orderBy('id','asc')->first();
			if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain) {
				$sku_url =  ($client->custom_domain);
			} else {
				$sku_url =  ($client->sub_domain.env('SUBMAINDOMAIN'));
			}

			$slug = str_replace(' ', '-',$request->product_name);
			$generated_slug = $sku_url.'.'.$slug;
			$slug = generateSlug($generated_slug);
			$slug = str_replace(' ', '-',$slug);
			$generated_slug = $sku_url.'.'.$slug;

            $users = Auth::user();
	
			$user = User::where('id',$users->id)->first();
			
			$user_vendor = UserVendor::where('user_id', $users->id)->first();
            if(empty($user_vendor)){
              
                $user->assignRole(4); // by default make this user as vendor
				
				$user->is_admin = 1;
				$user->save();

				// Create vendor with default images
				$vendor = new Vendor();
				$vendor->logo = 'default/default_logo.png';
				$vendor->banner = 'default/default_image.png';

				$vendor->status = 1;
                $vendor->show_slot = 0;
				$vendor->name = $user->name;
				$vendor->p2p = 1;
				$vendor->email = $user->email ?? '';
				$vendor->phone_no = $user->phone_number ?? '';
				$vendor->slug = Str::slug($user->name, "-");
				$vendor->save();
				$user_vendor =  UserVendor::create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
				$user = new User ;
				// $user->createPermissionsUser();
				$p2p_type = Type::where('service_type', 'p2p')->first();
				if( !empty($p2p_type) ) {
					$category_id = Category::where('type_id', $p2p_type->id)->get();
					$categories_ids = [];
					
					if( !empty($category_id) ) {
						foreach($category_id as $key => $val) {
							$categories_ids[] = $val->id;
						}
					}
					$request->request->add(['selectedCategories'=> $categories_ids]);
					
				}
				
				$this->addDataSaveVendor($request, $vendor->id);
				$user_vendor = UserVendor::where('user_id', $users->id)->first();
                   
                }
            
			if(@$user_vendor->vendor_id){
				$product = new Product();
				$product->sku = $slug;
				$product->url_slug = $generated_slug;
				$product->title = $request->product_name;        
				$product->category_id = $request->category_id;
				$product->description = $request->product_description ?? '';
                $product->body_html = $request->product_description ?? '';
				$product->type_id = 1;
				$product->is_live = 1;
				$product->publish_at = date('Y-m-d H:i:s');
				$product->vendor_id = $user_vendor->vendor_id;
				if(@$request->address){
					$product->address = $request->address;
				}
				if(@$request->lat){
					$product->latitude = $request->lat;
				}
                if(@$request->long){
					$product->longitude = $request->long;
				}
				$client_lang = ClientLanguage::where('is_primary', 1)->first();
				if (!$client_lang) {
					$client_lang = ClientLanguage::where('is_active', 1)->first();
				}
				$client_lang = ClientLanguage::where('is_primary', 1)->first();
				if (!$client_lang) {
					$client_lang = ClientLanguage::where('is_active', 1)->first();
				}
                
				$product->save();
				if ($product->id > 0) {
					$datatrans[] = [
						'title' => $request->product_name??null,
						'body_html' => $request->product_description??null,
						'meta_title' => '',
						'meta_keyword' => '',
						'meta_description' => '',
						'product_id' => $product->id,
						'language_id' => $client_lang->language_id
					];
					$product_category = new ProductCategory();
					$product_category->product_id = $product->id;
					$product_category->category_id = $request->category_id;
					$product_category->save();
					$proVariant = new ProductVariant();
					$proVariant->price = $request->price ?? 0;
					
                    $week_price = ($request->price *4 / 7);
                    $month_price = ($request->price *4 * 3 / 30);

                    $proVariant->week_price = round($week_price) ?? 0;
                
                    $proVariant->month_price = round($month_price) ?? 0;
					
					if(@$request->emirate){
						$proVariant->emirate = $request->emirate;
					}
					if(@$request->compare_at_price){
						$proVariant->compare_at_price = $request->compare_at_price;
					}

					if(@$request->minimum_duration){
						$proVariant->minimum_duration = $request->minimum_duration * 24;
					}

                    if ($request->has('p2p_price') && $request->filled('p2p_price')) {
						$proVariant->price = $request->p2p_price ?? 0;
					}

					$proVariant->sku = $slug;
					$proVariant->title =$slug . '-' .  empty($request->product_name) ?$slug : $request->product_name;
					$proVariant->product_id = $product->id;
					$proVariant->quantity = 1;            
					$proVariant->status = 1;            
					$proVariant->barcode = $this->generateBarcodeNumber();
					$proVariant->save();
					ProductTranslation::insert($datatrans);
					
					$product_detail = Product::where('id', $product->id)->firstOrFail();
					
					$data = ['product_detail' => $product_detail];


					// Upload Image
					
                    $fileIds = $this->uploadProductImages($product, $request);

                    $this->uploadProductImage360($request, $product);
					

                    // Add Attributes
                    $this->addProductAttribute($request, $product);

                    // Add Attributes
					$this->addProductAvailability($request, $product);

					
                    $toaster = $this->successToaster(__('Success'),__('Product added successfully') );
                    return redirect()->back()->with('toaster', $toaster);
				
			}else{
                $toaster = $this->errorToaster(__('ERROR'),'Sorry, You are not a vendor.' );
                return redirect()->back()->with('toaster', $toaster);
			}
		}else{
            $toaster = $this->errorToaster(__('ERROR'),'Sorry, You are not a vendor.' );
            return redirect()->back()->with('toaster', $toaster);
            
		}
		   
        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('ERROR'),$e->getMessage() );
            return redirect()->back()->with('toaster', $toaster);

        }
	 }

     public function addDataSaveVendor(Request $request, $vendor_id){

        $vendor = Vendor::where('id', $vendor_id)->firstOrFail();
        $VendorController = new VendorController();

        $request->merge(["return_json"=>1]);
        $VendorConfigrespons = $VendorController->updateConfig($request,'',$vendor_id)->getData();//$this->updateConfig($vendor_id);
       // pr($VendorConfigrespons);
        if($request->has('can_add_category')){
            $vendor->add_category = $request->can_add_category == 'on' ? 1 : 0;
        }
        if ($request->has('assignTo')) {
            $vendor->vendor_templete_id = $request->assignTo;
        }

        $vendor->save();
        if($request->has('category_ids')){
            foreach($request->category_ids as $category_id){
                VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
            }
        }
        if($request->has('selectedCategories')){
            foreach($request->selectedCategories as $category_id){
                VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
            }
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Vendor created Successfully!',
            'data' => $VendorConfigrespons
        ]);
        // pr($VendorConfigrespons);
    }


     function uploadProductImage360($request, $product){

        if ($request->has('file_360')) {
            $imageId = '';
            $files = $request->file('file_360');
            if(is_array($files)) {
                foreach ($files as $file) {
                    $img = new VendorMedia();
                    $img->media_type = 4;
                    $img->vendor_id = $product->vendor_id;
                    $img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
                    $img->save();
                    $path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
                    if ($img->id > 0) {
                        $imageId = $img->id;
                        $image = new ProductImage();
                        $image->product_id = $product->id;
                        $image->is_default = 1;
                        $image->media_id = $imageId;
                        $image->save();
                                                
                    }
                }
                //return response()->json(['htmlData' => $resp]);
            } else {
                $img = new VendorMedia();
                $img->media_type = 4;
                $img->vendor_id = $product->vendor_id;
                $img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
                $img->save();					
                if ($img->id > 0) {
                    $imageId = $img->id;
                    $image = new ProductImage();
                    $image->product_id = $product->id;
                    $image->is_default = 1;
                    $image->media_id = $img->id;
                    $image->save();
                                    
                }
            }					
        }
        return true;
     }

     function addProductAvailability($request, $product){
        if( @$request->date_availability) {
            // dd("dgf");
            $dates = explode(' - ',$request->date_availability);
            $start_date = $dates[0];
            $end_date = $dates[1];
            $date_availability = getDatesBetweenTwoDates($start_date, $end_date);
            $date_availability_data = [];
            foreach($date_availability as $date_availability){
                $date_availability_data[] = [
                    'product_id' => $product->id,
                    'date_time' => $date_availability,
                    'not_available' => 0,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ];
            }
            if(@$date_availability_data){
                ProductAvailability::insert($date_availability_data);
            }
            
        }
        return true;
     }

     function addProductAttribute($request, $product){
        if( checkTableExists('product_attributes') ) {
            if( !empty($request->attribute) ) {
                $attribute = $request->attribute;
                
                if( !empty($attribute) ) {
            
                    $insert_arr = [];
                    $insert_count = 0;
                    // \Log::info($attribute);
                    foreach($attribute as $key => $value) {
                        // \Log::info($value);
                        if( !empty($value) && !empty($value['option'] && is_array($value) )) {
                            
                            if(!empty($value['type']) && $value['type'] == 1 ) { // dropdown
                                $value_arr = @$value['value'];
                                
                                foreach( $value['option'] as $key1 => $val1 ) {
                                    if( @in_array($val1['option_id'], $value_arr) ) {

                                        $insert_arr[$insert_count]['product_id'] = $product->id;
                                        $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                        $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                        $insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
                                        $insert_arr[$insert_count]['key_value'] = $val1['option_id'];
                                        $insert_arr[$insert_count]['latitude'] = null;
                                        $insert_arr[$insert_count]['longitude'] = null;
                                        $insert_arr[$insert_count]['is_active'] = 1;
                                    }
                                    $insert_count++;
                                }
                            }
                            else {
                                $value_arr = @$value['value'];
                                
                                // \Log::info($option['option_id']);
                                foreach($value['option'] as $option_key => $option) {
                                    if(!empty($value['type']) && $value['type'] == 4 ) { // textbox
                                        $insert_arr[$insert_count]['product_id'] = $product->id;
                                        $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                        $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                        $insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
                                        $insert_arr[$insert_count]['key_value'] = (!empty($value['value']) && !empty($value['value'][0]) ? $value['value'][0] : '');
                                        $insert_arr[$insert_count]['latitude'] = null;
                                        $insert_arr[$insert_count]['longitude'] = null;
                                        $insert_arr[$insert_count]['is_active'] = 1;
                                    }
                                    elseif(!empty($value['type']) && $value['type'] == 6) {
                                        
                                        $insert_arr[$insert_count]['product_id'] = $product->id;
                                        $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                        $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                        $insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
                                        $insert_arr[$insert_count]['key_value'] = $value['address'];
                                        $insert_arr[$insert_count]['latitude'] = $value['latitude'] ?? null;
                                        $insert_arr[$insert_count]['longitude'] = $value['longitude'] ?? null;
                                        $insert_arr[$insert_count]['is_active'] = 1;
                                    }
                                    elseif( @in_array($option['option_id'], $value_arr) ) {
                                        // \Log::info($option);
                                        $insert_arr[$insert_count]['product_id'] = $product->id;
                                        $insert_arr[$insert_count]['attribute_id'] = $value['id'];
                                        $insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
                                        $insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
                                        $insert_arr[$insert_count]['key_value'] = $option['option_id'];
                                        $insert_arr[$insert_count]['latitude'] = $value['latitude'] ?? null;
                                        $insert_arr[$insert_count]['longitude'] = $value['longitude'] ?? null;
                                        $insert_arr[$insert_count]['is_active'] = 1;
                                    }
                                    
                                    $insert_count++;
                                }
                            }
                        }

                    
                    }
                    if( !empty($insert_arr) ) {
                        ProductAttribute::where('product_id',$request->product_id)->delete();
                        ProductAttribute::insert($insert_arr);
                    }
                }
            }
        }
        return true;
     }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
   /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Product  $product
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function saveProduct($request)
    {

        $client = Client::orderBy('id','asc')->first();
        if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain) {
            $sku_url =  ($client->custom_domain);
        } else {
            $sku_url =  ($client->sub_domain.env('SUBMAINDOMAIN'));
        }

        $slug = $this->generateSlug($request->product_name);
        $slug = str_replace(' ', '-',$slug);
        $generated_slug = $sku_url.'.'.$slug;
        $user = Auth::user();	
        $user_vendor = UserVendor::where('user_id', $user->id)->first();
        if(@$user_vendor->vendor_id || $user->is_superadmin == 1){
            $product = new Product();
            $product->sku = $slug;
            $product->url_slug = $generated_slug;
            $product->title = empty($request->product_name) ? $request->sku : $request->product_name;
            $product->type_id = $request->type_id ?? 1;
            $product->category_id = $request->category_id;
            $product->vendor_id = $user_vendor->vendor_id ?? $user->id;
            $product->is_live = 1;
            $product->publish_at = date('Y-m-d H:i:s');
            $client_lang = ClientLanguage::where('is_primary', 1)->first();
            if (!$client_lang) {
                $client_lang = ClientLanguage::where('is_active', 1)->first();
            }
            $product->save();
            
            if ($product->id > 0) {
                $datatrans[] = [
                    'title' => $request->product_name??null,
                    'body_html' => !empty($request->product_description)?$request->product_description:'',
                    'meta_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'product_id' => $product->id,
                    'language_id' => $client_lang->language_id
                ];
                $product_category = new ProductCategory();
                $product_category->product_id = $product->id;
                $product_category->category_id = $request->category_id;
                $product_category->save();
                $proVariant = new ProductVariant();
                $proVariant->price = $request->daily_price;
                $proVariant->sku =$slug;
                $proVariant->title =$slug . '-' .  empty($request->product_name) ?$slug : $request->product_name;
                $proVariant->product_id = $product->id;
                $proVariant->barcode = $this->generateBarcodeNumber();
                $proVariant->quantity = 1;            
                $proVariant->status = 1;
                $proVariant->week_price = $request->week_price ?? 0;
                $proVariant->month_price = $request->monthly_price ?? 0;
               
                if (@$request->compare_at_price) {
                    $proVariant->compare_at_price = $request->compare_at_price;
                }
                if (@$request->minimum_duration) {
                    $proVariant->minimum_duration = $request->minimum_duration * 24;
                }
                $proVariant->save();
                ProductTranslation::insert($datatrans);
                
                if (@$request->date_availability){
                    // Define your start and end dates
                    // Initialize an empty array to store the dates
                    $date_availability_data = [];
                    if(strpos($request->date_availability, 'to') !== false){
                        $dates = explode('to', $request->date_availability);
                        $start_date = Carbon::parse(@$dates[0]);
                        $end_date = Carbon::parse(@$dates[1]);
                        
                        // Loop through the dates and add them to the array
                        while ($start_date->lte($end_date)) {
                            $date_availability_data[] = [
                                'product_id' => $product->id,
                                'date_time' => $start_date->toDateString(),
                                'not_available' => 0,
                                'created_at' => Carbon::now(),
                                'updated_at' => Carbon::now()
                            ];
                            $start_date->addDay(); // Increment the date by one day
                        }
                    }
                    if (!empty(@$date_availability_data)) {
                        ProductAvailability::insert($date_availability_data);
                    }
                }
                // Add Availability for the product
                if (@$request->date_availability && is_array($request->date_availability)) {
                    $date_availability_data = [];
                    foreach ($request->date_availability as $date_availability) {
                        $date_availability_data[] = [
                            'product_id' => $product->id,
                            'date_time' => $date_availability['date_time'],
                            'not_available' => $date_availability['not_available'],
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now()
                        ];
                    }
                    
                }

                return $product;
            }
            else{
                throw new \ErrorException('Sorry, You are not a vendor.', 400);
            }
        }else{
            throw new \ErrorException('Sorry, You are not a vendor.', 400);
        }
    }

    private function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }


    public function uploadProductImages($product, $request)
    {
        
        if ($request->has('file')) {
            $imageId = [];
            $files = $request->file('file');
            if (is_array($files)) {
                foreach ($files as $file) {
                    $img = new VendorMedia();
                    $img->media_type = 1;
                    $img->vendor_id = $product->vendor_id;
                    $img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
                    $img->save();
                    $path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
                    if ($img->id > 0) {
                        $imageId[] = $img->id;
                        $image = new ProductImage();
                        $image->product_id = $product->id;
                        $image->is_default = 1;
                        $image->media_id = $img->id;
                        $image->save();
                       
                    }
                }
                
            } 
            // dd($imageId);
           
            return $imageId;
        }

    }

    public function generateSlug($name)
    {
        if (Product::whereSku($slug = $name)->exists()) {
            $max = Product::whereSku($name)->latest('id')->value('sku');
            if (isset($max[-1]) && is_numeric($max[-1])) {
                return preg_replace_callback('/(\d+)$/', function($mathces) {
                    return $mathces[1] + 1;
                }, $max);
            }
            return $slug.'-'.rand();
        }
        return $slug;
    }
}
