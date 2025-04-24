<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Controllers\Client\BaseController;
use App\Models\{InfluencerCategory, ClientLanguage, InfluencerAttribute, InfluencerAttributeCategory, InfluencerAttributeTranslation, InfluencerAttributeOption, InfluencerAttributeOptionTranslation, Category};
use Session;
use Auth;

class InfluencerAttributeController extends BaseController
{
    public function create(Request $request)
    {
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        
        $influencer_categories = InfluencerCategory::where('is_active', 1)->get();
        
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                ->where('is_active', 1)
                ->orderBy('is_primary', 'desc')->get();

        
        $returnHTML = view('backend.influencerreferandearn.add-influencer-attribute')->with(['categories' => $influencer_categories,  'languages' => $langs])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    public function store(Request $request)
    {
        if($request->cate_id ==''){
            return redirect()->back()->with('error_delete',__('Please select Category!'));
        }
        
        $v_pos = InfluencerAttribute::select('id','position')->where('position', \DB::raw("(select max(`position`) from variants)"))->first();
        $variant = new InfluencerAttribute();
        $variant->title = (!empty($request->title[0])) ? $request->title[0] : '';
        $variant->type = $request->type;
        $variant->position = 1;
        if($v_pos){
            $variant->position = $v_pos->position + 1;
        }
        $variant->save();
        $data = $data_cate = array();
        if($variant->id > 0){
            $data_cate['attribute_id'] = $variant->id;
            $data_cate['category_id'] = $request->cate_id;
            InfluencerAttributeCategory::insert($data_cate);
            foreach ($request->title as $key => $value) {
                $varTrans = new InfluencerAttributeTranslation();
                $varTrans->title = $request->title[$key];
                $varTrans->attribute_id = $variant->id;
                $varTrans->language_id = $request->language_id[$key];
                $varTrans->save();
            }

            foreach ($request->hexacode as $key => $value) {

                $varOpt = new InfluencerAttributeOption();
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
            InfluencerAttributeOptionTranslation::insert($data);
        }
        return redirect()->back()->with('success', 'Attribute added successfully!');
        
    }

    public function edit($domain = '', $id)
    {
       
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $variant = InfluencerAttribute::select('id', 'title', 'type', 'position')
                        ->with('translation') //, 'option.translation', 'varcategory'
                        ->where('id', $id)->firstOrFail();
                
        $categories = InfluencerCategory::where('is_active', 1)->get();
        $langs = ClientLanguage::with(['language', 'variantTrans' => function($query) use ($id) {
                        $query->where('variant_id', $id);
                    }])
                    ->select('language_id', 'is_primary', 'is_active')
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'desc')->get();
            
        $category_id = '';
        if( !empty($variant->option) && !empty($variant->option->first()) && !empty($variant->option->first()->attribute_id) ) {
            $attribute_id = $variant->option->first()->attribute_id;
            $category_id = InfluencerAttributeCategory::where('attribute_id', $attribute_id)->value('category_id');
        }
        
        $submitUrl = route('attribute-influencer-refer-earn.update', $id);

        $returnHTML = view('backend.influencerreferandearn.edit-attribute')->with(['categories' => $categories,  'languages' => $langs, 'variant' => $variant, 'category_id' => $category_id])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
    }

    public function update(Request $request, $domain = '', $id)
    {
        if($request->cate_id ==''){
            return redirect()->back()->with('error_delete',__('Please select Category!'));
        }
        
            $variant = InfluencerAttribute::where('id', $id)->firstOrFail();
            $variant->title = $request->title[0];
            $variant->type = $request->type;
            $variant->save();

            $VariantCategory = InfluencerAttributeCategory::where('attribute_id', $variant->id)->first();
            if(!empty($VariantCategory)):
                $affected = InfluencerAttributeCategory::where('attribute_id', $variant->id)->update(['category_id' => $request->cate_id]);
            else:
                $affected = InfluencerAttributeCategory::insert(['attribute_id' => $variant->id, 'category_id' => $request->cate_id]);
            endif;

            foreach ($request->language_id as $key => $value) {

                $varTrans = InfluencerAttributeTranslation::where('language_id', $value)->where('attribute_id', $variant->id)->first();
                if(!$varTrans){
                    $varTrans = new InfluencerAttributeTranslation();
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
                    $varOpt = InfluencerAttributeOption::where('id', $value)->first();

                    if(!$varOpt){
                        $varOpt = new InfluencerAttributeOption();
                        $varOpt->attribute_id = $variant->id;
                    }

                    $varOpt->title = $request->opt_title[$curLangId][$key];
                    $varOpt->hexacode = ($request->hexacode[$key] == '') ? '' : $request->hexacode[$key];
                    $varOpt->save();
                    $exist_options[$key] = $varOpt->id;
                }else{

                    $varOpt = new InfluencerAttributeOption();
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
                        $varOptTrans = InfluencerAttributeOptionTranslation::where('language_id', $lid)->where('attribute_option_id', $value)->first();
                        if(!$varOptTrans){
                            $varOptTrans = new InfluencerAttributeOptionTranslation();
                            $varOptTrans->attribute_option_id =$exist_options[$key];
                            $varOptTrans->language_id = $lid;
                        }
                        $varOptTrans->title = $request->opt_title[$lid][$key];
                        $varOptTrans->save();

                    }else{
                        $varOptTrans = new InfluencerAttributeOptionTranslation();
                        $varOptTrans->attribute_option_id =$exist_options[$key];
                        $varOptTrans->language_id = $lid;
                        $varOptTrans->title = $request->opt_title[$lid][$key];
                        $varOptTrans->save();
                    }
                }
            }
            return redirect()->back()->with('success', 'Attribute updated successfully!');
        
    }


    public function delete($domain = '', $id)
    {
        $var = InfluencerAttribute::where('id', $id)->first();
        $var->status = 2;
        $var->save();
        return redirect()->back()->with('success', 'Attribute deleted successfully!');
    }

}
