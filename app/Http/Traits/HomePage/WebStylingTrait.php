<?php

namespace App\Http\Traits\HomePage;

use App\Models\{Category, HomePageLabel, HomeProduct, Product, Vendor, VendorCategory};
use Carbon\Carbon;
use Session, DB;
use Illuminate\Support\Str;


trait WebStylingTrait
{

    public function getCategoryListing()
    {
        return $p_categories = Category::with(['parent', 'translation_one'])
            ->whereIn('type_id', ['1', '3', '7', '8', '9'])
            ->where('id', '>', '1')
            ->where('deleted_at', NULL)
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')
            ->orderBy('position', 'asc')
            ->orderBy('id', 'asc')
            ->get();
    }

    public function homePageLabelExists($slug)
    {
        return HomePageLabel::where('slug', '=', $slug)->exists();
    }

    public function getCategories($slug)
    {
        $single_category_products = [];

        if ($this->homePageLabelExists($slug)) {
            $single_category_products['categories'] = $this->getCategoryListing();
            $single_category_products['slug'] = $slug;
        }
        return $single_category_products;
    }

    public function updateSingleCategoryProductsToDb($request)
    {


        if (checkTableExists('home_products')) {
            $insert = ['slug' => 'single_category_products', 'product_category' => $request->product_category];
            HomeProduct::updateOrCreate(
                ['slug' => $insert['slug']],
                ['category_id' => $insert['product_category']]
            );
        }
        return true;
    }
    public function updateSelectedProductstoDb($id, $request,$type='')
    {
        // $delete = HomeProduct::where('layout_id', $id);
        
        // if(!empty($type))
        // $delete = $delete->where('type',$type);

        // $delete = $delete->delete();
        // foreach($request->selected_products as $products){
            $relatedArray = [
                'slug' => 'selected_products',
                // 'product_id' => $products,
                'products' => json_encode($request->selected_products),
                'layout_id'=> $id,
                'type'      => $type??0
            ];
        // }
        HomeProduct::updateOrCreate(['layout_id' => $id], $relatedArray);
        return true;
    }


    public function getSingleCategoryProducts($slug)
    {
        if (checkTableExists('home_products')) {
            return HomeProduct::where('slug', $slug)->first();
        }
        return [];
    }

    public function getSelectedProducts()
    {
        $product_ids = [];
        
        $single_category_products = HomeProduct::whereSlug('selected_products')->first();
        if (!empty($single_category_products->products)) {

            $product_ids = json_decode($single_category_products->products);
        } else {
            $product_ids = [];
        }
        
        return $product_ids;
    }

    public function getHomePageSelectedProducts($type=0)
    {
        //0 for web and 1 for App type
        $selectedIds = HomeProduct::where(['type' => $type, 'slug' => 'selected_products'])->latest()->value('products');
        return json_decode($selectedIds);
    }

    public function getProducts($request = [],$all='')
    {
        $language_id = Session::get('customerLanguage') ?? 1;
        $products = Product::with([
        'translation' => function ($q) use ($language_id){
            $q->select('product_id', 'title')->where('language_id', $language_id);
        }]);

        //If not need all product 
        if(empty($all)){
            if(@$request['category_id']){
                $products->wherehas('category', function($q) use($request){
                    $q->where('category_id', $request['category_id']);
                });
            }
            if(@$request['products']){
                $products->whereIn('id', $request['products']);
            }
        }

        $products = $products->where('is_live', 1)->select('id', 'title')->get();
        return $products;
    }
}