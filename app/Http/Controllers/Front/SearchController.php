<?php

namespace App\Http\Controllers\Front;

use Session;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,VendorTrait};
use Illuminate\Support\Facades\Redis;
use App\Http\Controllers\Front\FrontController;
use App\Models\{Currency, Banner, Category, Brand, Product, ClientLanguage, Vendor, ClientCurrency, Category_translation, ProductTranslation};

class SearchController extends FrontController
{
    use ApiResponser,VendorTrait;
    public function postAutocompleteSearch(Request $request)
    {
        $response = [];
        $keyword = $request->input('keyword');
        $language_id = Session::get('customerLanguage');
       // $preferences = getClientPreferenceDetail();
        $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences'):  getClientPreferenceDetail();

        $latitude = session('latitude');
        $longitude = session('longitude');
        $selectedAddress = session('selectedPlaceId');
        $vendorType = Session::get('vendorType');
        $allowed_vendors = $this->getServiceAreaVendors();

        $vendors = Vendor::byVendorSubscriptionRule($preferences)->select('id', 'name', 'logo', 'slug', 'show_slot');
        if (count($allowed_vendors) > 0) {
            $vendors = $vendors->whereIn('id', $allowed_vendors);
        }


        if (@$preferences) {
            if ((empty($latitude)) && (empty($longitude)) && (empty($selectedAddress))) {
                $selectedAddress = @$preferences->Default_location_name;
                $latitude = @$preferences->Default_latitude;
                $longitude = @$preferences->Default_longitude;
                Session::put('latitude', $latitude);
                Session::put('longitude', $longitude);
                Session::put('selectedAddress', $selectedAddress);
            }
            if (($preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {
                if (!empty($latitude) && !empty($longitude)) {
                    $vendors = $vendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                        $query->select('vendor_id')
                            ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }

                if (isset($preferences->slots_with_service_area) && ($preferences->slots_with_service_area == 1)) {
                    $slot_vendors = clone $vendors;
                    $data = $slot_vendors->get();
                    foreach ($data as $key => $value) {
                        $vendors = $vendors->when(($value->show_slot == 0), function ($query) use ($latitude, $longitude) {
                            return $query->where(function ($query1) use ($latitude, $longitude) {
                                $query1->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                    ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                        $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                    });
                            });
                        });
                    }
                }
            }
        }
        $vendors = $vendors->where(function ($q) use ($keyword) {
            $q->where('name', 'LIKE', "%$keyword%");
        })->where('status', '!=', 2)->get();
        $vender_results = [];
        foreach ($vendors as $vendor) {
            $vendor->redirect_url = route('vendorDetail', $vendor->slug);
            $vendor->image_url = $vendor->logo['proxy_url'] . '80/80' . $vendor->logo['image_path'];
            $vender_results[] = $vendor;
        }


        $products = Product::with(['media', 'vendor'])->join('product_translations as pt', 'pt.product_id', 'products.id')->join('vendors', 'vendors.id', 'products.vendor_id')
            ->select('products.id', 'products.sku', 'products.url_slug', 'pt.title  as dataname', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description', 'products.vendor_id', 'vendors.slug as vendor_slug')
            ->where('pt.language_id', $language_id)
            ->where(function ($q) use ($keyword) {
                $q->where('products.sku', ' LIKE', '%' . $keyword . '%')->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
            })->where('products.is_live', 1);
        //if( (isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) ){
        $products = $products->whereIn('vendor_id', $allowed_vendors);
        //}
        $products = $products->whereNull('deleted_at')->groupBy('products.id')->get();
        // $product_results = [];
        foreach ($products as $product) {
            $redirect_url = route('productDetail', [$product->vendor_slug, $product->url_slug]);
            $image_url = $product->media->first() ? $product->media->first()->image->path['proxy_url'] . '80/80' . $product->media->first()->image->path['image_path'] : '';
            $product_results[] = ['id' => $product->id, 'name' => $product->dataname, 'image_url' => $image_url, 'redirect_url' => $redirect_url];
        }
        if (@$product_results) {
            $response[] = ['title' => '', 'result' => $product_results];
        }
        if (@$vender_results) {
            $response[] = ['title' => __('Vendors'), 'result' => $vender_results];
        }

        $brands = Brand::join('brand_translations as bt', 'bt.brand_id', 'brands.id')
            ->select('brands.id', 'bt.title as name', 'image')
            ->where('bt.title', 'LIKE', '%' . $keyword . '%')
            ->where('brands.status', '!=', '2')
            ->where('bt.language_id', $language_id)
            ->orderBy('brands.position', 'asc')->get();
        $brand_results = [];
        foreach ($brands as $brand) {
            $brand->redirect_url = route('brandDetail', $brand->id);
            $brand->image_url = $brand->image['proxy_url'] . '80/80' . $brand->image['image_path'];
            $brand_results[] = $brand;
        }

        if (@$brand_results) {
            $response[] = ['title' => __('Brands'), 'result' => $brand_results];
        }

        $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
            ->leftjoin('types', 'types.id', 'categories.type_id')
            ->select('categories.id', 'categories.icon', 'categories.image', 'categories.slug', 'categories.parent_id', 'cts.name', 'categories.warning_page_id', 'categories.template_type_id', 'types.title as redirect_to')
            ->where('categories.id', '>', '1')
            ->where('categories.is_visible', 1)
            ->where('categories.status', '!=', 2)
            ->where('categories.is_core', 1)
            ->where('cts.language_id', $language_id);
        // if($preferences){
        //    $categories =  $categories->leftJoin('vendor_categories as vct', 'categories.id', 'vct.category_id')
        //                   ->where(function ($q1) use ($vendors, $status, $lang_id) {
        //                       $q1->whereIn('vct.vendor_id', $allowed_vendors)
        //                           ->where('vct.status', 1)
        //                           ->orWhere(function ($q2) {
        //                               $q2->whereIn('categories.type_id', [4,5,8]);
        //                           });
        //                   });
        //       }
        $categories = $categories->where(function ($q) use ($keyword) {
            $q->where('cts.name', ' LIKE', '%' . $keyword . '%')
                ->orWhere('categories.slug', 'LIKE', '%' . $keyword . '%')
                ->orWhere('cts.trans-slug', 'LIKE', '%' . $keyword . '%');
        })->orderBy('categories.parent_id', 'asc')
            ->orderBy('categories.position', 'asc')->get();
        $category_results = [];
        foreach ($categories as $category) {
            $redirect_url = route('categoryDetail', $category->slug);
            $image_url = $category->image['proxy_url'] . '80/80' . $category->image['image_path'];
            $category_results[] = ['id' => $category->id, 'name' => $category->name, 'image_url' => $image_url, 'redirect_url' => $redirect_url];
        }
        if (@$category_results) {
            $response[] = ['title' => __('Categories'), 'result' => $category_results];
        }


        // dd($response);
        return $this->successResponse($response);
    }

    public function showSearchResults($domain = "", $keyword)
    {

        $response = [];
        $mapViewVendorList = [];
        $keyword = $keyword;
        $language_id = Session::get('customerLanguage');
      // $preferences = Session::get('preferences');
        $preferences = !empty(Session::get('preferences')) ? (object)Session::get('preferences'):  getClientPreferenceDetail();
        $vendorType = Session::get('vendorType');
        $vendorMapView = '';
        $vendors = Vendor::byVendorSubscriptionRule($preferences)->select('id', 'name', 'logo', 'slug', 'latitude', 'longitude', 'address', 'dial_code', 'phone_no')->where($vendorType, 1);

        if (@$preferences) {
            if ((empty($latitude)) && (empty($longitude)) && (empty($selectedAddress))) {
                $selectedAddress = @$preferences->Default_location_name;
                $latitude = @$preferences->Default_latitude;
                $longitude = @$preferences->Default_longitude;
                Session::put('latitude', $latitude);
                Session::put('longitude', $longitude);
                Session::put('selectedAddress', $selectedAddress);
            }
            if ((@$preferences->is_hyperlocal == 1) && ($latitude) && ($longitude)) {

                if (!empty($latitude) && !empty($longitude)) {
                    $vendors = $vendors->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                        $query->select('vendor_id')
                            ->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                    });
                }
            }
            if ((@$preferences->map_on_search_screen == 1)) {
                $vendorMapView = 1;
            }
        }



        $vender_results = [];
        $vendors = $vendors->where(function ($q) use ($keyword) {
            $q->where('name', 'LIKE', "%$keyword%");
        })->where('status', '!=', 2)->get();
        foreach ($vendors as $vendor) {
            $vendor->redirect_url = route('vendorDetail', $vendor->slug);
            $vendor->image_url = $vendor->logo['proxy_url'] . '300/300' . $vendor->logo['image_path'];
            $vender_results[] = $vendor;
            if (!empty($vendorMapView)) {
                $mapViewVendorList[] = $vendor;
            }
        }


        $products = Product::with('media')->join('product_translations as pt', 'pt.product_id', 'products.id')->join('vendors', 'vendors.id', 'products.vendor_id')
            ->select(
                'products.id',
                'products.sku',
                'pt.title  as dataname',
                'pt.body_html',
                'pt.meta_title',
                'pt.meta_keyword',
                'pt.meta_description',
                'products.vendor_id',
                'vendors.name as vendor_name',
                'vendors.slug as vendor_slug',
                'products.url_slug',
                'vendors.latitude',
                'vendors.longitude',
                'vendors.address',
                'vendors.dial_code',
                'vendors.phone_no'
            )
            ->where('pt.language_id', $language_id)
            ->whereHas('vendor', function ($query) use ($vendorType) {
                $query->where($vendorType, 1);
            })
            ->where(function ($q) use ($keyword) {
                $q->where('products.sku', ' LIKE', '%' . $keyword . '%')->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
            })->where('products.is_live', 1)->whereNull('deleted_at')->groupBy('products.id')->get();
        $product_results  = [];
        foreach ($products as $product) {
            $redirect_url = route('productDetail', [$product->vendor_slug, $product->url_slug]);
            $image_url = $product->media->first() ? $product->media->first()->image->path['proxy_url'] . '300/300' . $product->media->first()->image->path['image_path'] : '';
            $product_results[] = ['id' => $product->id, 'name' => $product->dataname, 'latitude' => $product->latitude, 'longitude' => $product->longitude, 'address' => $product->address, 'image_url' => $image_url, 'redirect_url' => $redirect_url];
            if (!empty($vendorMapView)) {
                $vendorRedirectUrl = route('vendorDetail', $product->vendor_slug);
                $mapViewVendorList[] = ['id' => $product->vendor_id, 'name' => $product->vendor_name, 'dial_code' => (!empty($product->dial_code) ? $product->dial_code : ''), 'phone_no' => $product->phone_no, 'latitude' => $product->latitude, 'longitude' => $product->longitude, 'address' => $product->address, 'image_url' => $image_url, 'redirect_url' => $vendorRedirectUrl];;
            }
        }

        if (@$product_results) {
            $response[] = ['title' => '', 'result' => $product_results];
        }


        if (@$vender_results) {
            $response[] = ['title' => __('Vendors'), 'result' => $vender_results];
        }
        $brands = Brand::join('brand_translations as bt', 'bt.brand_id', 'brands.id')
            ->select('brands.id', 'bt.title as name', 'image')
            ->where('bt.title', 'LIKE', '%' . $keyword . '%')
            ->where('brands.status', '!=', '2')
            ->where('bt.language_id', $language_id)
            ->orderBy('brands.position', 'asc')->get();
        $brand_results = [];
        foreach ($brands as $brand) {
            $brand->redirect_url = route('brandDetail', $brand->id);
            $brand->image_url = $brand->image['proxy_url'] . '300/300' . $brand->image['image_path'];
            $brand_results[] = $brand;
        }

        if (@$brand_results) {
            $response[] = ['title' => __('Brands'), 'result' => $brand_results];
        }

        $categories = Category::join('category_translations as cts', 'categories.id', 'cts.category_id')
            ->leftjoin('types', 'types.id', 'categories.type_id')
            ->leftjoin('vendors', 'vendors.id', 'categories.vendor_id')
            ->select(
                'categories.id',
                'categories.icon',
                'categories.image',
                'categories.slug',
                'categories.parent_id',
                'cts.name',
                'categories.warning_page_id',
                'categories.template_type_id',
                'types.title as redirect_to',
                'vendors.id as vendor_id',
                'vendors.name as vendor_name',
                'vendors.slug as vendor_slug',
                'vendors.latitude',
                'vendors.longitude',
                'vendors.address',
                'vendors.dial_code',
                'vendors.phone_no'
            )
            ->where('categories.id', '>', '1')
            ->where('categories.is_visible', 1)
            ->where('categories.status', '!=', 2)
            ->where('categories.is_core', 1)
            ->where('cts.language_id', $language_id)
            ->where(function ($q) use ($keyword) {
                $q->where('cts.name', ' LIKE', '%' . $keyword . '%')
                    ->orWhere('categories.slug', 'LIKE', '%' . $keyword . '%')
                    ->orWhere('cts.trans-slug', 'LIKE', '%' . $keyword . '%');
            })->orderBy('categories.parent_id', 'asc')
            ->orderBy('categories.position', 'asc')->get();
        $category_results = [];
        foreach ($categories as $category) {
            $redirect_url = route('categoryDetail', $category->slug);
            $image_url = $category->image['proxy_url'] . '300/300' . $category->image['image_path'];
            $category_results[] = ['id' => $category->id, 'name' => $category->name, 'latitude' => $category->latitude, 'longitude' => $category->longitude, 'address' => $category->address, 'image_url' => $image_url, 'redirect_url' => $redirect_url];
            if (!empty($vendorMapView)) {
                $vendorRedirectUrl = route('vendorDetail', $category->vendor_slug);
                $mapViewVendorList[] = ['id' => $category->vendor_id, 'name' => $category->vendor_name, 'dial_code' => (!empty($category->dial_code) ? $category->dial_code : ''), 'phone_no' => $category->phone_no, 'latitude' => $category->latitude, 'longitude' => $category->longitude, 'address' => $category->address, 'image_url' => $image_url, 'redirect_url' => $vendorRedirectUrl];;
            }
        }

        if (@$category_results) {
            $response[] = ['title' => __('Categories'), 'result' => $category_results];
        }



        $language_id = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($language_id);
        $vendorLatLong = [];

        if (!empty($response)) {
            foreach ($response as $res) {
                if (!empty($res['latitude']) && !empty($res['longitude'])) {
                    $addres[] = [$res['latitude'], $res['longitude']];
                    $vendorLatLong = $addres;
                }
            }
        }
        // dd($response);

        return view('frontend.searchResults')->with(['listData' => $response, 'mapViewVendorList' => $mapViewVendorList, 'vendorMapView' => $vendorMapView, 'vendorLatLong' => $vendorLatLong, 'navCategories' => $navCategories, 'keyword' => $keyword]);
    }
}
