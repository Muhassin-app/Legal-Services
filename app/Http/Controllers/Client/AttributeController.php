<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Client\BaseController;
use App\Models\{Attribute, AttributeOption, AttributeTranslation, AttributeOptionTranslation, AttributeCategory, Category, ClientLanguage, ProductAttribute, Product};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
class AttributeController extends BaseController
{
    private $blockdata = 2;

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $categories = Category::with(['translation' => function($q) use($langId){
                $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                ->where('category_translations.language_id', $langId);
            }])
            ->where('status', 1)
            ->orderBy('parent_id', 'asc')
            ->orderBy('position', 'asc')
            ->whereIn('type_id', ['1', '3', '6','10', '13', '7','14']) //see type ids in TypeSeeder seeder
            ->where('id', '>', 1)
            ->whereNull('vendor_id')
            ->get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                ->where('is_active', 1)
                ->orderBy('is_primary', 'desc')->get();

        $categories_hierarchy = '';
        if($categories){
            $categories_build = $this->buildTree($categories->toArray());
            $categories_hierarchy = $this->printCategoryOptionsHeirarchy($categories_build);
        }
        
        $returnHTML = view('backend.catalog.add-attribute')->with(['categories' => $categories_hierarchy,  'languages' => $langs])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

   
        if($request->cate_id ==''){
            return redirect()->back()->with('error_delete',__('Please select Category!'));
        }
        if(
            checkTableExists('attributes') && 
            checkTableExists('attribute_categories') && 
            checkTableExists('attribute_option_translations') && 
            checkTableExists('attribute_options') && 
            checkTableExists('attribute_translations') 
        ){ 
            $v_pos = Attribute::select('id','position')->where('position', \DB::raw("(select max(`position`) from variants)"))->first();
            $variant = new Attribute();
            $variant->title = (!empty($request->title[0])) ? $request->title[0] : '';
            $variant->user_id = Auth::id();
            $variant->type = $request->type;
            $variant->position = 1;
            if ($request->hasFile('icon')) {
                $filePath = 'attributes/' . \Str::random(40);
                $file = $request->file('icon');
                $orignal_name = $request->file('icon')->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                $url = Storage::disk('s3')->url($file_name);
                $variant->icon = $url;
            }
            if($v_pos){
                $variant->position = $v_pos->position + 1;
            }
            $variant->save();
            $data = $data_cate = array();
            if($variant->id > 0){

                foreach($request->cate_id as $category_id)
                {
                    $data_cate['attribute_id'] = $variant->id;
                
                    $data_cate['category_id'] = $category_id;
                    AttributeCategory::insert($data_cate);

                }
               
                foreach ($request->title as $key => $value) {
                    $varTrans = new AttributeTranslation();
                    $varTrans->title = $request->title[$key];
                    $varTrans->attribute_id = $variant->id;
                    $varTrans->language_id = $request->language_id[$key];
                    $varTrans->save();
                }

                foreach ($request->hexacode as $key => $value) {

                    $varOpt = new AttributeOption();
                    $varOpt->title = $request->opt_color[0][$key];
                    $varOpt->attribute_id = $variant->id;
                    $varOpt->hexacode = ($value == '') ? '' : $value;
                    $varOpt->save();

                    foreach($request->language_id as $k => $v) {
                        $data[] = [
                            'title' => $request->opt_color[$k][$key],
                            'attribute_option_id' => $varOpt->id,
                            'language_id' => $v
                        ];
                    }
                }
                AttributeOptionTranslation::insert($data);
            }
            return redirect()->back()->with('success', 'Attribute added successfully!');
        }
        else {
            return redirect()->back()->with(array('error_delete' => 'Attribute not added !'));
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id)
    {
        if(
            checkTableExists('attributes') && 
            checkTableExists('categories') && 
            checkTableExists('client_languages') 
        ){ 
            $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
            $variant = Attribute::select('id', 'title', 'type', 'position')
                            ->with('translation', 'option.translation', 'varcategory')
                            ->where('id', $id)->firstOrFail();
            $categories = Category::with(['translation' => function($q) use($langId){
                    $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                    ->where('category_translations.language_id', $langId);
                }])
                ->where('status', 1)
                ->orderBy('parent_id', 'asc')
                ->orderBy('position', 'asc')
                ->whereIn('type_id', ['1', '3', '6', '13', '10', '7','14'])
                ->where('id', '>', 1)
                ->whereNull('vendor_id')
                ->get();
            $categories_hierarchy = '';
            if($categories){
                $categories_build = $this->buildTree($categories->toArray());
                $categories_hierarchy = $this->printCategoryOptionsHeirarchy($categories_build);
            }
            $langs = ClientLanguage::with(['language', 'variantTrans' => function($query) use ($id) {
                            $query->where('variant_id', $id);
                        }])
                        ->select('language_id', 'is_primary', 'is_active')
                        ->where('is_active', 1)
                        ->orderBy('is_primary', 'desc')->get();
            $submitUrl = route('attribute.update', $id);

            $returnHTML = view('backend.catalog.edit-attribute')->with(['categories' => $categories_hierarchy,  'languages' => $langs, 'variant' => $variant])->render();
            return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
        }
        else {
            return response()->json(array('success' => false));
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id)
    {
        if($request->cate_id ==''){
            return redirect()->back()->with('error_delete',__('Please select Category!'));
        }
        if(
            checkTableExists('attributes') && 
            checkTableExists('attribute_categories') && 
            checkTableExists('attribute_option_translations') && 
            checkTableExists('attribute_options') && 
            checkTableExists('attribute_translations') 
        ){ 
            $variant = Attribute::where('id', $id)->firstOrFail();
            $variant->title = $request->title[0];
            $variant->type = $request->type;
            $variant->user_id = Auth::id();
            if ($request->hasFile('icon')) {
                $filePath = 'attributes/' . \Str::random(40);
                $file = $request->file('icon');
                $orignal_name = $request->file('icon')->getClientOriginalName();
                $file_name = Storage::disk('s3')->put($filePath, $file, 'public');
                $url = Storage::disk('s3')->url($file_name);
                $variant->icon = $url;
            }
            $variant->save();

            $VariantCategory = AttributeCategory::where('attribute_id', $variant->id)->get();
            if(!empty($VariantCategory)):
                foreach($request->cate_id as $category_id)
                {

                    AttributeCategory::updateOrInsert(
                        ['attribute_id' => $variant->id, 'category_id' => $category_id],
                        [
                        'attribute_id' =>  $variant->id,
                        'category_id' => $category_id
                        ]
                    );

                }
            else:
                foreach($request->cate_id as $category_id)
                {

                    AttributeCategory::updateOrInsert(
                        ['attribute_id' => $variant->id, 'category_id' => $category_id],
                        [
                        'attribute_id' =>  $variant->id,
                        'category_id' => $category_id
                        ]
                    );

                }
            endif;

            foreach ($request->language_id as $key => $value) {

                $varTrans = AttributeTranslation::where('language_id', $value)->where('attribute_id', $variant->id)->first();
                if(!$varTrans){
                    $varTrans = new AttributeTranslation();
                    $varTrans->attribute_id = $variant->id;
                    $varTrans->language_id = $value;
                }
                $varTrans->title = $request->title[$key];
                $varTrans->save();
            }

            $exist_options = array();
            foreach ($request->option_id as $key => $value) {

                $curLangId = $request->language_id[0];

                if(!empty($value)){
                    $varOpt = AttributeOption::where('id', $value)->first();

                    if(!$varOpt){
                        $varOpt = new AttributeOption();
                        $varOpt->attribute_id = $variant->id;
                    }

                    $varOpt->title = $request->opt_title[$curLangId][$key];
                    $varOpt->hexacode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                    $varOpt->save();
                    $exist_options[$key] = $varOpt->id;
                }else{

                    $varOpt = new AttributeOption();
                    $varOpt->attribute_id = $variant->id;
                    $varOpt->title = $request->opt_title[$curLangId][$key];
                    $varOpt->hexacode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                    $varOpt->save();
                    $exist_options[$key] = $varOpt->id;

                }
            }

            foreach($request->opt_id as $lid => $options) {

                foreach($options as $key => $value) {

                    if(!empty($value)){
                        $varOptTrans = AttributeOptionTranslation::where('language_id', $lid)->where('attribute_option_id', $value)->first();
                        if(!$varOptTrans){
                            $varOptTrans = new AttributeOptionTranslation();
                            $varOptTrans->attribute_option_id =$exist_options[$key];
                            $varOptTrans->language_id = $lid;
                        }
                        $varOptTrans->title = $request->opt_title[$lid][$key];
                        $varOptTrans->save();

                    }else{
                        $varOptTrans = new AttributeOptionTranslation();
                        $varOptTrans->attribute_option_id =$exist_options[$key];
                        $varOptTrans->language_id = $lid;
                        $varOptTrans->title = $request->opt_title[$lid][$key];
                        $varOptTrans->save();
                    }
                }
            }
            return redirect()->back()->with('success', 'Attribute updated successfully!');
        }
        else {
            return redirect()->back()->with('error_delete', 'Attribute not updated successfully!');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category_translation  $category_translation
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        if(checkTableExists('attributes')) {
            $var = Attribute::where('id', $id)->first();
            $var->status = 2;
            $var->save();
            return redirect()->back()->with('success', 'Attribute deleted successfully!');
        }
        else {
            return redirect()->back()->with('error_delete', 'Attribute not deleted successfully!');
        }
    }

    /**
     * save the order of variant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function updateOrders(Request $request)
    {
        $arr = explode(',', $request->orderData);
        foreach ($arr as $key => $value) {
            $variant = Attribute::where('id', $value)->first();
            if($variant){
                $variant->position = $key + 1;
                $variant->save();
            }
        }
        return redirect('client/category')->with('success', 'Attribute order updated successfully!');
    }

    /**
     * save the order of variant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function makeHtmlData($variants)
    {
        $html = '<div class="row mb-2">';
      
        foreach ($variants as $vk => $var) {
            $html .= '<div class="col-sm-3"> <label class="control-label">'.$var->title.'</label> </div> 
                    <div class="col-sm-9">';
            foreach ($var->option as $key => $opt) {
                $html .='<div class="checkbox checkbox-success form-check-inline pr-3">
                    <input type="checkbox" name="variant'.$var->id.'" class="intpCheck" opt="'.$opt->id.';'.$opt->title.'" varId="'.$var->id.';'.$var->title.'" id="opt_vid_'.$opt->id.'"> 
                    <label  for="opt_vid_'.$opt->id.'">'.$opt->title.'</label></div>';
            }

            $html .='</div>';
        }
        $html .='<div>';
        return $html;
    }

    /**
     * save the order of variant.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Variant  $variant
     * @return \Illuminate\Http\Response
     */
    public function variantbyCategory($domain = '', $cid)
    {
        if(checkTableExists('attributes')) {
            $variants = Attribute::with('option', 'varcategory.cate.english')
                            ->select('variants.*')
                            ->join('variant_categories', 'variant_categories.variant_id', 'variants.id')
                            ->where('variant_categories.category_id', $cid)
                            ->where('variants.status', '!=', 2)
                            ->orderBy('position', 'asc')->get();

            $makeHtml = $this->makeHtmlData($variants);
            return response()->json(array('success' => true, 'resp'=>$makeHtml));
        }
        else {
            return response()->json(array('success' => false, 'resp'=>''));
        }
    }

    function deleteAttribute(Request $request) {
        try {
            if(checkTableExists('attributes')) {
                if( !empty($request->id) ) {
                    $attr_option_id = $request->id;
                    AttributeOption::where('id', $attr_option_id)->delete();
                    return response()->json(array('success' => true));
                }
                return response()->json(array('success' => false));
            }
            else {
                return response()->json(array('success' => false));
            }
        }
        catch(\Exception $e) {
            return response()->json(array('success' => false));
        }
    }
    
    function updateAttributeOption(Request $request) {
        
        try {
            if($request->cate_id =='' && $request->id ==''){
                return redirect()->back()->with('error_delete',__('Please select Category!'));
            }

            if(
                checkTableExists('attributes') && 
                checkTableExists('attribute_categories') && 
                checkTableExists('attribute_option_translations') && 
                checkTableExists('attribute_options') && 
                checkTableExists('attribute_translations') 
            ){ 

                $id = $request->id;
                $variant = Attribute::where('id', $id)->firstOrFail();
                $variant->title = $request->title[0];
                $variant->type = $request->type;
                $variant->user_id = Auth::id();
                $variant->save();

                $VariantCategory = AttributeCategory::where('attribute_id', $variant->id)->first();
                if(!empty($VariantCategory)):
                    $affected = AttributeCategory::where('attribute_id', $variant->id)->update(['category_id' => $request->cate_id]);
                else:
                    $affected = AttributeCategory::insert(['attribute_id' => $variant->id, 'category_id' => $request->cate_id]);
                endif;

                foreach ($request->language_id as $key => $value) {

                    $varTrans = AttributeTranslation::where('language_id', $value)->where('attribute_id', $variant->id)->first();
                    if(!$varTrans){
                        $varTrans = new AttributeTranslation();
                        $varTrans->attribute_id = $variant->id;
                        $varTrans->language_id = $value;
                    }
                    $varTrans->title = $request->title[$key];
                    $varTrans->save();
                }

                $exist_options = $insert_arr = array();
                
                // Before insert data to product attribute first delete with same attribute
                // ProductAttribute::where(['product_id' => $request->product_id, 'attribute_id' => $id])->delete();
                $insert_count = 0;

                foreach ($request->option_id as $key => $value) {

                    $curLangId = $request->language_id[0];

                    if(!empty($value)){

                        $varOpt = AttributeOption::where('id', $value)->first();

                        if(!$varOpt){
                            $varOpt = new AttributeOption();
                            $varOpt->attribute_id = $variant->id;

                            // Create array to store in product attribute
                            $insert_arr[$insert_count]['attribute_id'] = $id;
                            $insert_arr[$insert_count]['product_id'] = $request->product_id;
                            $insert_arr[$insert_count]['key_name'] = $variant->title;
                            $insert_arr[$insert_count]['attribute_option_id'] = $varOpt->id;
                            $insert_arr[$insert_count]['key_value'] = ($request->type == 1) ? $varOpt->id : $request->opt_title[$curLangId][$key];
                            $insert_arr[$insert_count]['is_active'] = 1;
                        }

                        $varOpt->title = $request->opt_title[$curLangId][$key];
                        $varOpt->hexacode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                        $varOpt->save();
                        $exist_options[$key] = $varOpt->id;

                        
                    }else{
                        $varOpt = new AttributeOption();
                        $varOpt->attribute_id = $variant->id;
                        $varOpt->title = $request->opt_title[$curLangId][$key];
                        $varOpt->hexacode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                        $varOpt->save();
                        $exist_options[$key] = $varOpt->id;
                        
                        // Create array to store in product attribute
                        $insert_arr[$insert_count]['attribute_id'] = $id;
                        $insert_arr[$insert_count]['product_id'] = $request->product_id;
                        $insert_arr[$insert_count]['key_name'] = $variant->title;
                        $insert_arr[$insert_count]['attribute_option_id'] = $varOpt->id;
                        $insert_arr[$insert_count]['key_value'] = ($request->type == 1) ? $varOpt->id : $request->opt_title[$curLangId][$key];
                        $insert_arr[$insert_count]['is_active'] = 1;
                    }
                    $insert_count++;
                }
                
                foreach($request->opt_id as $lid => $options) {

                    foreach($options as $key => $value) {

                        if(!empty($value)){
                            $varOptTrans = AttributeOptionTranslation::where('language_id', $lid)->where('attribute_option_id', $value)->first();
                            if(!$varOptTrans){
                                $varOptTrans = new AttributeOptionTranslation();
                                $varOptTrans->attribute_option_id =$exist_options[$key];
                                $varOptTrans->language_id = $lid;
                            }
                            $varOptTrans->title = $request->opt_title[$lid][$key];
                            $varOptTrans->save();

                        }else{
                            $varOptTrans = new AttributeOptionTranslation();
                            $varOptTrans->attribute_option_id =$exist_options[$key];
                            $varOptTrans->language_id = $lid;
                            $varOptTrans->title = $request->opt_title[$lid][$key];
                            $varOptTrans->save();
                        }
                    }
                }

                // Save attribute with related product
                ProductAttribute::insert($insert_arr);

                // create block and append to attribute section
                $productAttributes = Attribute::with('option', 'varcategory.cate.primary')
                    ->select('attributes.*')
                    ->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
                    ->where('attribute_categories.category_id', $request->cate_id)
                    ->where('attributes.status', '!=', 2)
                    ->orderBy('position', 'asc')->get();

                $product = Product::with('ProductAttribute')->where('id', $request->product_id)->firstOrFail();

                $attribute_key_value = $attribute_value = array();
                if( !empty($product->ProductAttribute) ) {
                        foreach($product->ProductAttribute as $key => $val) {
                        $attribute_value[] = $val->attribute_option_id;
                        $attribute_key_value[$val->attribute_option_id] = $val->key_value;
                    }
                }
                $html = view('layouts.shared.product-attribute')->with(['productAttributes' => $productAttributes,  'attribute_key_value' => $attribute_key_value, 'attribute_value' => $attribute_value])->render();
                return response()->json(array('success' => true, 'html' => $html));
            }
            else {
                return response()->json(array('success' => false, 'html' => ''));
            }
        }
        catch(\Exception $e) {
            return response()->json(array('success' => false));
        }
    }
}
