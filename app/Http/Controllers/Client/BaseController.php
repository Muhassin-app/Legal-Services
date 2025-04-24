<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{Client, Category, Product, ClientPreference, UserDevice, UserLoyaltyPoint, Wallet, VendorSavedPaymentMethods, Nomenclature,NomenclatureTranslation};
use Illuminate\Support\Facades\Storage;
use Session;
use GuzzleHttp\Client as GCLIENT;

class BaseController extends Controller
{
    private $htmlData = '';
    private $toggleData = '';
    private $optionData = ''; 
    private $categoryOptionData = []; 
    private $successCount = 0;
    private $parent_cat_id = 0;
    private $makeArray = array();

    public function buildTree($elements, $parentId = "1")
    {
        $branch = array();
        foreach ($elements as $element) {
            if(isset($element['parent_id']) && !empty($element['parent_id'])){
                if ($element['parent_id'] == $parentId) {
                    $children = $this->buildTree($elements, $element['id']);
                    if ($children) {
                        $element['children'] = $children;
                    }
                    $branch[] = $element;
                }
            }   
        }
        return $branch;
    }

    /*      Category tree on vendor col-3 and category  page    */
    public function printTree($tree, $from = 'category', $activeCategory = [], $blockedCategory = [], $html = '')
    {   
        if (!is_null($tree) && count($tree) > 0) {
            $this->htmlData .= '<ol class="dd-list">';
            foreach ($tree as $node) {
                if(!isset($node['translation_one'])){
                    continue;
                }
                if(!empty($activeCategory)){
                    if (in_array($node['id'], $activeCategory)) {
                        $this->htmlData .= '<li class="dd-item dd3-item catid'.$node["id"].'" data-id="' . $node["id"] . '">';
                        if ($from == 'category') {
                            $this->htmlData .= '<div class="dd-handle dd3-handle"></div>';
                        }
                        $icon = $node['icon']['proxy_url'] . '30/30' . $node['icon']['image_path'];
                        $is_vendr = ($from == 'vendor' && $node["is_core"] == 0)?1:0;
                        if (isset($node['translation_one'])) {
                            $this->htmlData .= '<div class="dd3-content"><div class="dd-img d-flex align-items-center"><img class="rounded-circle mr-1" src="' . $icon . '"><a class="openCategoryModal" dataid="' . $node["id"] . '" is_vendor="'.$is_vendr.'" href="#"> ' . $node['translation_one']["name"] . '</a></div><span class="inner-div text-right">';
                        } else {
                            $this->htmlData .= '<div class="dd3-content"><div class="dd-img d-flex align-items-center"><img class="rounded-circle mr-1" src="' . $icon . '">' . $node['translation_one']["name"] . '</div><span class="inner-div text-right">';
                        }
                        if (!in_array($node["id"], $blockedCategory)) {
                            $status = 2; //$icon = 'mdi-lock-open-variant';
                            $title = 'Delete';
                            $icon = 'mdi-delete';
                            $askMessage = "return confirm('Are you sure? You want to delete category.')";
                            if ($from == 'category') {
                                if ($node["is_core"] == 1) {
                                    $this->htmlData .= '<a class="action-icon openCategoryModal" dataid="' . $node["id"] . '" is_vendor="0" href="#"> <i class="mdi mdi-square-edit-outline"></i></a><a class="action-icon" dataid="' . $node["id"] . '" title="' . $title . '" onclick="' . $askMessage . '" href="' . url("client/category/delete/" . $node["id"]) . '"> <i class="mdi ' . $icon . '"></i></a>';
                                }
                            } elseif ($from == 'vendor' && $node["is_core"] == 0) {
                                // $this->htmlData .= '<a class="action-icon openCategoryModal" dataid="' . $node["id"] . '" is_vendor="1" href="#"> <i class="mdi mdi-square-edit-outline"></i></a>
                                // <a class="action-icon" dataid="' . $node["id"] . '" onclick="' . $askMessage . '" href="' . url("client/category/delete/" . $node["id"]) . '" title="' . $title . '"> <i class="mdi ' . $icon . '"></i></a>';
                                $this->htmlData .= '<a class="action-icon openCategoryModal" dataid="' . $node["id"] . '" is_vendor="1" href="#"> <i class="mdi mdi-square-edit-outline"></i></a>';

                                $this->htmlData .= '<button type="button" class="btn btn-primary-outline action-icon delete-category" title="' . $title . '" data-destroy_url="' . url("client/category/delete/" . $node["id"]) . '" data-rel="'.$node["id"].'"><i class="mdi mdi-delete"></i></button>';
                            }
                        }

                        

                        $this->htmlData .= '</span> </div>';
                        if (isset($node['children']) && count($node['children']) > 0) {
                            $ss = $this->printTree($node['children'], $from, $activeCategory, $blockedCategory);
                        }
                        $this->htmlData .= '</li>';
                    }
                }else{
                    if($node['type_id'] == 4 || $node['type_id']==5 || $node['type_id']==1 || $node['type_id']==3){
                        $this->htmlData .= '<li class="dd-item dd3-item dd-nochildren catid'.$node["id"].'" data-id="' . $node["id"] . '">';
                    } else {
                        $this->htmlData .= '<li class="dd-item dd3-item catid'.$node["id"].'" data-id="' . $node["id"] . '">';
                    }
                        if ($from == 'category') {
                            $this->htmlData .= '<div class="dd-handle dd3-handle"></div>';
                        }
                        $icon = $node['icon']['proxy_url'] . '30/30' . $node['icon']['image_path'];
                        if (isset($node['translation_one'])) {
                            $this->htmlData .= '<div class="dd3-content"><div class="dd-img d-flex align-items-center"><img class="rounded-circle mr-1" src="' . $icon . '"><a class="openCategoryModal ellips" dataid="' . $node["id"] . '" is_vendor="0" href="#"> ' . $node['translation_one']["name"] .' (' . @$node['type']["title"] . ') </a></div><span class="inner-div text-right">';
                        } else {
                            $this->htmlData .= '<div class="dd3-content"><div class="dd-img d-flex align-items-center"><img class="rounded-circle mr-1" src="' . $icon . '">' . $node['translation_one']["name"] . '</div><span class="inner-div text-right">';
                        }
                        if (!in_array($node["id"], $blockedCategory)) {
                            $status = 2; //$icon = 'mdi-lock-open-variant';
                            $title = 'Delete';
                            $icon = 'mdi-delete';
                            //$askMessage = "return confirm('Are you sure? You want to delete category.')";
                            $askMessage = "deleteCategory(".$node['id'].")";
                            // $askMessage = "return Swal.fire({title: 'Are you sure? You want to delete category.', showCancelButton:true,confirmButtonText: 'Ok',}).then((result) => {
                            //     if (result.isConfirmed) {
                            //       Swal.fire(
                            //         'Deleted!',                                    
                            //         'success'
                            //       )
                            //     }
                            //   })";
                            if ($from == 'category') {
                                if ($node["is_core"] == 1) {
                                    $this->htmlData .= '<a class="action-icon openCategoryModal" dataid="' . $node["id"] . '" is_vendor="0" href="#"> <i class="mdi mdi-square-edit-outline"></i></a><a class="action-icon" dataid="' . $node["id"] . '" title="' . $title . '" onclick="' . $askMessage . '" href="#"> <i class="mdi ' . $icon . '"></i></a>';
                                }
                            } elseif ($from == 'vendor' && $node["is_core"] == 0) {
                                $this->htmlData .= '<a class="action-icon openCategoryModal" dataid="' . $node["id"] . '" is_vendor="1" href="#"> <i class="mdi mdi-square-edit-outline"></i></a>
                                <a class="action-icon" dataid="' . $node["id"] . '" onclick="' . $askMessage . '" href="#" title="' . $title . '"> <i class="mdi ' . $icon . '"></i></a>';
                            }
                        }
                        $this->htmlData .= '</span> </div>';
                        if (isset($node['children']) && count($node['children']) > 0) {
                            $ss = $this->printTree($node['children'], $from, $activeCategory, $blockedCategory);
                        }
                        $this->htmlData .= '</li>';
                }
            }
            // pr($this->htmlData);
            $this->htmlData .= '</ol>';
        }
        return $this->htmlData;
    }

    public function getParentCategories($child, $langId, $parentCategories=[]){
        $category = Category::with(['translation' => function($q) use($langId){
            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')->where('category_translations.language_id', $langId)->groupBy(['category_translations.language_id', 'category_translations.category_id']);
        }])->where('id', $child)->where('status', 1)->select('id', 'slug', 'parent_id')->first();
        if($category){
            $parentCategories[] = $category->translation->first() ? $category->translation->first()->name : $category->slug;
            if($category->parent_id != 1){                
                $parentCategories = $this->getParentCategories($category->parent_id, $langId, $parentCategories);
            }
        }
        return $parentCategories;
    }

    /*      Category options heirarchy      */
    public function getCategoryOptionsHeirarchy($tree, $langId)
    {
        if (!is_null($tree) && count($tree) > 0) {
            foreach ($tree as $key => $node) {

                // type_id 1 means product in type table
                if (isset($node['children']) && count($node['children']) > 0) {
                    
                    // start including parent category
                    $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];

                    $parentCategories = array_reverse($this->getParentCategories($node['id'], $langId));
                    $hierarchyName = implode(' > ', $parentCategories);

                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // end including parent category

                    $this->getCategoryOptionsHeirarchy($node['children'], $langId);
                }
                else{
                    // if ($node['type_id'] == 1 || $node['type_id'] == 3 || $node['type_id'] == 7 || $node['type_id'] == 8) {
                        $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                        $parentCategories = array_reverse($this->getParentCategories($node['id'], $langId));
                        $hierarchyName = implode(' > ', $parentCategories);
                        
                        $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // }
                }
            }
        }
        return $this->categoryOptionData;
    }

    /*      Category options heirarchy      */
    public function printCategoryOptionsHeirarchy($tree, $parentCategory = [])
    {
        if (!is_null($tree) && count($tree) > 0) {
            foreach ($tree as $key => $node) {
                if($node['parent_id'] == 1){
                    $parentCategory = array($node['translation'][0]['name']??'');
                }
                // type_id 1 means product in type table
                if (isset($node['children']) && count($node['children']) > 0) {
                    if($node['parent_id'] != 1 && !empty($node['translation'][0]['name'])){
                        $parentCategory[] = $node['translation'][0]['name'];
                    }
                    
                    // start including parent category
                    $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                    $hierarchyName = $category; // assume first category is parent
                    if(count($parentCategory) > 0){
                        if($node['parent_id'] != 1){ // if category is not parent then make heirarchy
                            $hierarchyName = implode(' > ', $parentCategory);
                            $hierarchyName = $hierarchyName.' > '.$category;
                        }
                    }
                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // end including parent category

                    $this->printCategoryOptionsHeirarchy($node['children'], $parentCategory);
                }
                else{
                    // if ($node['type_id'] == 1 || $node['type_id'] == 3 || $node['type_id'] == 7 || $node['type_id'] == 8) {
                        $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                        if($node['parent_id'] == 1){
                            $parentCategory = [];
                            $hierarchyName = $category;
                        }else{
                            $hierarchyName = implode(' > ', $parentCategory);
                            $hierarchyName = $hierarchyName.' > '.$category;
                        }
                        // $this->optionData .= '<option value="'.$node['id'].'">'.$hierarchyName.'</option>';
                        $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    // }
                }
            }
        }
        return $this->categoryOptionData;
    }


    //function created by surendra singh-----------------------------//
    public function printCategoryOptionsHeirarchy_new($tree, $parentCategory = [])
    {
        if (!is_null($tree) && count($tree) > 0) {
            foreach ($tree as $key => $node) {
                $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                if (!isset($node['children'])) {
                    if($node['parent_id'] == 1){
                        $parentCategory = [];
                        $hierarchyName = $category;
                    }else{
                        $hierarchyName = implode(' > ', $parentCategory);
                        $hierarchyName = $hierarchyName.' > '.$category;
                    }
                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                }
            }

            foreach ($tree as $key => $node) { 
                
                $category = (isset($node['translation'][0]['name'])) ? $node['translation'][0]['name'] : $node['slug'];
                if(isset($node['children']) && count($node['children']) > 0) {
                    $parentCategory[] = $category;
                    $hierarchyName = '';
                    if(count($parentCategory) > 0){
                        if($node['parent_id'] != 1){ // if category is not parent then make heirarchy
                            $hierarchyName = implode(' > ', $parentCategory);
                        }
                    }
                    $this->categoryOptionData[] = array('id'=>$node['id'], 'type_id'=>$node['type_id'], 'hierarchy'=>$hierarchyName, 'category'=>$category, 'can_add_products'=>$node['can_add_products']);
                    //$hierarchyName = implode(' > ', $parentCategory);
                    //$hierarchyName = $hierarchyName.' > '.$category;
                    $this->printCategoryOptionsHeirarchy_new($node['children'], $parentCategory);
                }
            }
        }
        
        return $this->categoryOptionData;
    }

    /*      Category tree for vendor to enable & disable category      */
    public function printTreeToggle($tree, $activeCategory = [])
    {
        if (!is_null($tree) && count($tree) > 0) {
            $this->toggleData .= '<ol class="dd-list">';
            foreach ($tree as $node) {
                // type_id 1 means product in type table
                if ($node['type_id'] == 1 || $node['type_id'] == 3) {
                    $this->toggleData .= '<li class="dd-item dd3-item" data-id="' . $node["id"] . '">';
                    $icon = $node['icon']['proxy_url'] . '30/30' . $node['icon']['image_path'];
                    $this->toggleData .= '<div class="dd3-content"><div class="dd-img d-flex align-items-center"><img class="rounded-circle mr-1" src="' . $icon . '">' . $node["slug"] . '</div><span class="inner-div text-right">';
                    $name = 'category[' . $node["id"] . ']';
                    $this->toggleData .= '<a class="action-icon" data-id="' . $node["id"] . '" href="javascript:void(0)">';
                    if ($node['type_id'] == 3) {
                        if (in_array($node["id"], $activeCategory) && $node["parent_id"] == 3) {
                            $this->toggleData .= '<input class="form-control" type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '"  data-color="#43bee1" data-plugin="switchery" checked>';
                        } elseif (in_array($node["id"], $activeCategory) && in_array($node["parent_id"], $activeCategory)) {
                            $this->toggleData .= '<input class="form-control" type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '"  data-color="#43bee1" data-plugin="switchery" checked>';
                        } else {
                            $this->toggleData .= '<input type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '" data-color="#43bee1" class="form-control activeCategory"  data-plugin="switchery">';
                        }
                    } else {
                        if (in_array($node["id"], $activeCategory) && $node["parent_id"] == 1) {
                            $this->toggleData .= '<input class="form-control" type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '"  data-color="#43bee1" data-plugin="switchery" checked>';
                        } elseif (in_array($node["id"], $activeCategory) && in_array($node["parent_id"], $activeCategory)) {
                            $this->toggleData .= '<input class="form-control" type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '"  data-color="#43bee1" data-plugin="switchery" checked>';
                        } else {
                            $this->toggleData .= '<input type="checkbox" data-id="' . $node["id"] . '" name="' . $name . '" data-color="#43bee1" class="form-control activeCategory"  data-plugin="switchery">';
                        }
                    }
                    $this->toggleData .= '<input type="hidden" name="category_id[]" value="' . $node["id"] . '">';
                    $this->toggleData .= '</a></span> </div>';
                    if (isset($node['children']) && count($node['children']) > 0) {
                        $ss = $this->printTreeToggle($node['children'], $activeCategory);
                    }
                    $this->toggleData .= '</li>';
                }
            }
            $this->toggleData .= '</ol>';
        }
        return $this->toggleData;
    }

    function buildArray($elements, $parentId = 1, $count = 0)
    {
        $branch = array();
        $acCount = $count + 1;

        $did = 0;
        foreach ($elements as $key => $element) {
            if (!empty($element->id)) {
                $did = $element->id;
                $branch[$key]['id'] = $element->id;
                $branch[$key]['parent_id'] = $parentId;
                $category = Category::where('id', $element->id)->first();
                $category->parent_id = $parentId;
                $category->position = $key + 1;
                if ($category->save()) {
                    $this->successCount = $this->successCount + 1;
                }
            }

            if (isset($element->children) && !empty($element->children)) {
                $children = $this->buildArray($element->children, $did, $acCount);
                if ($children) {
                    $branch[$key]['child'] = $children;
                }
            }
            $count++;
        }
        return $this->successCount;
    }

    public function userMetaData($userid, $device_type = 'web', $device_token = 'web')
    {
        $device = UserDevice::where('user_id', $userid)->first();
        if (!$device) {
            $user_device[] = [
                'user_id' => $userid,
                'device_type' => $device_type,
                'device_token' => $device_token,
                'access_token' => ''
            ];

            UserDevice::insert($user_device);
        }

        $loyaltyPoints = UserLoyaltyPoint::where('user_id', $userid)->first();
        if (!$loyaltyPoints) {
            $loyalty[] = [
                'user_id' => $userid,
                'points' => 0
            ];
            UserLoyaltyPoint::insert($loyalty);
        }
        return 1;
    }

    /* Create random and unique client code*/
    public function randomData($table, $digit, $where)
    {
        $random_string = substr(md5(microtime()), 0, $digit);
        // after creating, check if string is already used
        while (\DB::table($table)->where($where, $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, $digit);
        }
        return $random_string;
    }

    public function randomBarcode($table)
    {
        $barCode = substr(md5(microtime()), 0, 14);
        while (\DB::table($table)->where('card_qr_code', $barCode)->exists()) {
            $barCode = substr(md5(microtime()), 0, 14);
        }
        return $barCode;
    }

    /* Save user payment method */
    public function saveVendorPaymentMethod($request)
    {
        $payment_method = new VendorSavedPaymentMethods;
        $payment_method->vendor_id = $request->vendor_id;
        $payment_method->user_id = $request->user_id;
        $payment_method->payment_option_id = $request->payment_option_id;
        $payment_method->card_last_four_digit = $request->card_last_four_digit;
        $payment_method->card_expiry_month = $request->card_expiry_month;
        $payment_method->card_expiry_year = $request->card_expiry_year;
        $payment_method->customerReference = ($request->has('customerReference')) ? $request->customerReference : NULL;
        $payment_method->cardReference = ($request->has('cardReference')) ? $request->cardReference : NULL;
        $payment_method->save();
    }

    /* Get Saved vendor payment method */
    public function getSavedVendorPaymentMethod($request)
    {
        $saved_payment_method = VendorSavedPaymentMethods::where('user_id', $request->user_id)
                        ->where('payment_option_id', $request->payment_option_id)->first();
        return $saved_payment_method;
    }

    public function getNomenclatureName($searchTerm, $langId, $plural = true){
        $result = Nomenclature::with(['translations' => function($q) use($langId) {
                    $q->where('language_id', $langId);
                }])->where('label', 'LIKE', "%{$searchTerm}%")->first();
        if($result){
            $searchTerm = $result->translations->count() != 0 ? $result->translations->first()->name : ucfirst($searchTerm);
        }
        return $plural ? $searchTerm : rtrim($searchTerm, 's');
    }

    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }
    
    public function fixedFee($lang_id){
        if(Nomenclature::where('label','Fixed Fee')->exists()){
            $nomenclatures_translation_id=Nomenclature::where('label','Fixed Fee')->first()->id;
            return NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->exists() ? NomenclatureTranslation::where(['nomenclature_id'=>$nomenclatures_translation_id,'language_id'=>$lang_id])->first()->name : "Fixed Fee Per Order";
        }else{
            return "Fixed Fee Per Order";
        }
    }


    # check if inventory system on 
    public function checkIfInventoryOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_inventory_service == 1 && !empty($preference->inventory_service_key_url) && !empty($preference->inventory_service_key_code))
            return $preference;
        else
            return false;
    }

    # get all store list from inventory system 
    public function getAllStoreListFromInventory(){
        try {

                $preference_data = $this->checkIfInventoryOn();
                if($preference_data != false) {
                    $preference = new GClient(['headers' => ['shortcode' => $preference_data->inventory_service_key_code,
                    'content-type' => 'application/json']
                        ]);

                    $url = $preference_data->inventory_service_key_url;
                    $res = $preference->get(
                    $url.'/api/v1/order-store-list',
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 200) { 
                        $data = $response;
                        $data['status'] = 200;
                        $data['client_preferences'] = $preference_data;
                        $data['message'] =  'Success';
                        return $data;
                    }else{
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  'Error';
                        return $data;
                    }
                }
                }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

    }
    

    # get All Product List From Inventory
    public function getAllProductListFromInventory($request){
        try {

                $preference_data = $this->checkIfInventoryOn();
                if($preference_data != false) {
                    $preference = new GClient(['headers' => ['shortcode' => $preference_data->inventory_service_key_code,
                    'content-type' => 'application/json']
                        ]);

                    $url = $preference_data->inventory_service_key_url;
                    $res = $preference->get(
                    $url.'/api/v1/product-list-by-vendor?vendor_id='.$request->vendor_id,
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 200) { 
                        $data = $response;
                        $data['status'] = 200;
                        $data['message'] =  'Success';
                        return $data;
                    }else{
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  'Error';
                        return $data;
                    }
                }
                }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

    }
    

    # get All Product List From Inventory with product ids
    public function getAllProductListFromInventoryByIds($productids){
        try {

                $preference_data = $this->checkIfInventoryOn();
                if($preference_data != false) {
                    $postdata =  ['productids' => $productids];
                    $preference = new GClient(['headers' => ['shortcode' => $preference_data->inventory_service_key_code,
                    'content-type' => 'application/json']
                        ]);

                    $url = $preference_data->inventory_service_key_url;
                    $res = $preference->POST(
                    $url.'/api/v1/product-list-by-productids',['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 200) { 
                        $data = $response;
                        $data['status'] = 200;
                        $data['message'] =  'Success';
                        return $data;
                    }else{
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  'Error';
                        return $data;
                    }
                }
                }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

    }


    # get All Category List From Inventory By Ids
    public function getAllCategoryListFromInventoryByIds($productids){
        try {

                $preference_data = $this->checkIfInventoryOn();
                if($preference_data != false) {
                    $postdata =  ['productids' => $productids];
                    $preference = new GClient(['headers' => ['shortcode' => $preference_data->inventory_service_key_code,
                    'content-type' => 'application/json']
                        ]);

                    $url = $preference_data->inventory_service_key_url;
                    $res = $preference->POST(
                    $url.'/api/v1/category-list-by-productids',['form_params' => ($postdata)]
                    );
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['status'] == 200) { 
                        $data = $response;
                        $data['status'] = 200;
                        $data['message'] =  'Success';
                        return $data;
                    }else{
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  'Error';
                        return $data;
                    }
                }
                }catch(\Exception $e)
                    {
                        $data = [];
                        $data['status'] = 400;
                        $data['message'] =  $e->getMessage();
                        return $data;

                    }

    }
    
    public function sendWalletNotification($user_id,$order_number)
    {
        $firebaseToken = UserDevice::select('device_token')->whereNotNull('device_token')->where('user_id',$user_id)->orderBy('id','desc')->limit(1)->pluck('device_token')->toArray();
        if(!empty($firebaseToken)){
            $preference = ClientPreference::select('fcm_server_key')->first();
            $fcm_server_key = !empty($preference->fcm_server_key)? $preference->fcm_server_key : 'null';
            
            $data = [
                "registration_ids" => $firebaseToken,
                "notification" => [
                    "title" => "Refund Added in Wallet",
                    "body" => 'Wallet has been <b>refunded</b> for cancellation or failed payment of order #' .$order_number
                ]
            ];
            $dataString = json_encode($data);
            $headers = [
                'Authorization: key=' . $fcm_server_key,
                'Content-Type: application/json',
            ];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
            $response = curl_exec($ch);
            curl_close($ch);
        }
        return true;
    }


    }


