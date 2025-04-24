<?php

namespace App\Http\Controllers\Client;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\ClientLanguage;
use App\Models\EstimateAddonSet;
use App\Http\Traits\ApiResponser;
use App\Models\AddonSetTranslation;
use App\Models\EstimateAddonOption;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Crypt;
use App\Models\EstimateAddonSetTranslation;
use App\Models\EstimateAddonOptionTranslation;

class EstimationAddonController extends Controller
{
    use ApiResponser;
    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
      
        $count = count($request->price);
     
        $min = ($request->min_select < 1) ? 0 : $request->min_select;
        $max = ($request->max_select < 1) ? 1 : $request->max_select;

        $min = ($min > $count) ? $count : $min;
        $max = ($max > $count) ? $count : $max;
        $max = ($max < $min) ? $min : $max;

        $addOn = new EstimateAddonSet();
        $addOn->title = $request->title[0];
        $addOn->min_select = $min;
        $addOn->max_select = $max;
        $addOn->position = 1;
        $addOn->save();
    
        if($addOn->id > 0){
            $setTrans = $optTrans = array();

            foreach ($request->language_id as $lk => $lang) {
                $setTrans[] = [
                    'title' => $request->title[$lk],
                    'estimate_addon_id' => $addOn->id,
                    'language_id' => $lang,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ];
            }
         
            EstimateAddonSetTranslation::insert($setTrans);
            
            foreach ($request->price as $key => $value) {

                $option = new EstimateAddonOption();
                $option->title = $request->opt_value[0][$key];
                $option->estimate_addon_id = $addOn->id;
                $option->position = $key + 1;
                $option->price = $value;
                $option->save();

                foreach ($request->language_id as $lk => $lang) {
                    $optTrans[] = [
                        'title' => $request->opt_value[$lk][$key],
                        'estimate_addon_opt_id' => $option->id,
                        'language_id' => $lang,
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];
                }
            }

            EstimateAddonOptionTranslation::insert($optTrans);

            return redirect()->back()->with('success', 'Variant added successfully!');
        }else{
            return redirect()->back()->with('error', 'Something went wrong!');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request)
    {
        $addon = EstimateAddonSet::with('translation', 'option.translation')->where('id', $request->id)->firstOrFail();
        

        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'desc')->get();

        $submitUrl = route('estimationsAddon.update', $request->id);
       

        $returnHTML = view('backend.vendor.edit-estimations-addon')->with(['languages' => $langs, 'addon' => $addon])->render();
        return response()->json(array('success' => true, 'min_select'=>$addon->min_select, 'max_select'=>$addon->max_select, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $update_id = Crypt::decrypt($request->update_id);
        $count = count($request->price);
        $min = ($request->min_select < 1) ? 0 : $request->min_select;
        $max = ($request->max_select < 1) ? 1 : $request->max_select;
        $min = ($min > $count) ? $count : $min;
        $max = ($max > $count) ? $count : $max;
        $max = ($max < $min) ? $min : $max;

        $addon = EstimateAddonSet::where('id', $update_id)->firstOrFail();

        $addon->title = $request->title[0];
        $addon->min_select = $min;
        $addon->max_select = $max;
        $addon->save();

        foreach ($request->language_id as $key => $value) {
            $varTrans = EstimateAddonSetTranslation::where('language_id', $value)->where('estimate_addon_id', $addon->id)->first();
            if(!$varTrans){
                $varTrans = new EstimateAddonSetTranslation();
                $varTrans->estimate_addon_id = $addon->id;
                $varTrans->language_id = $value;
            }
            $varTrans->title = $request->title[$key];
            $varTrans->save();
        }
        
        $exist_options = array();

        foreach ($request->option_id as $key => $value) {
            $curLangId = $request->language_id[0];
            if(empty($value)){
                $varOpt = new EstimateAddonOption();
                $varOpt->title = $request->opt_value[$curLangId][$key];
                $varOpt->estimate_addon_id = $addon->id;
                $varOpt->price = $request->price[$key];
                $varOpt->save();
                $exist_options[$key] = $varOpt->id;
            } else {
                $checkDbColumns = EstimateAddonOption::where('estimate_addon_id', $addon->id)->get();
                foreach($checkDbColumns as $check)
                {
                    if(!in_array($check->id, $request->option_id)){
                        $check->delete();
                    }
                }
                $varOpt = EstimateAddonOption::where('id', $value)->first();
                $varOpt->title = $request->opt_value[$curLangId][$key];
                $varOpt->price = $request->price[$key];
                $varOpt->save();
                $exist_options[$key] = $value;
            }
        }

        foreach($request->opt_id as $lid => $options) {
          
            foreach($options as $key => $value) {

                $checkDbColumns = EstimateAddonOptionTranslation::where('id', $value)->get();
           

               foreach($checkDbColumns as $check)
                {
                    if(!in_array($check->id, $request->estimate_addon_option_translations_id)){
                        $check->delete();
                    }
                }
          
                if(!empty($value)){
                    $setOptTrans = EstimateAddonOptionTranslation::where('language_id', $lid)->where('estimate_addon_opt_id', $value)->first();
             
                    if(!$setOptTrans){
                        $setOptTrans = new EstimateAddonOptionTranslation();
                        $setOptTrans->estimate_addon_opt_id =$exist_options[$key];
                        $setOptTrans->language_id = $lid;
                     }
                    $setOptTrans->title = $request->opt_value[$lid][$key];
                    $setOptTrans->save();
                }else{
                    $setOptTrans = new EstimateAddonOptionTranslation();
                    $setOptTrans->estimate_addon_opt_id = $exist_options[$key];
                    $setOptTrans->language_id = $lid;
                    $setOptTrans->title = $request->opt_value[$lid][$key];
                    $setOptTrans->save();
                }
            }
        }

        return redirect()->back()->with('success', 'Addon set updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        $id = Crypt::decrypt($request->id);
        $aos = EstimateAddonSet::where('id', $id)->first();
        $aos->status = 2;
        $aos->save();
        return redirect()->back()->with('success', 'Addon set deleted successfully!');
    }

    public function deletePreviousData($option_id, $opt_id)
    {
        $count = 1;
        foreach ($option_id as $key => $value) {
                if($count != 1){
                    EstimateAddonOption::where('id', $value)->delete();
                }
            $count++;
        }

        // foreach($opt_id as $lid => $options) {
        //     $optionCount = 1;
        //     foreach($options as $key => $value) {
        //         if($optionCount != 1){
        //             EstimateAddonOptionTranslation::where('language_id', $lid)->where('estimate_addon_opt_id', $value)->delete();
        //         } 
        //     $optionCount++;
        //     }
        // }
    }
}
