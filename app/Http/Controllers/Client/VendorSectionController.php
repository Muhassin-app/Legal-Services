<?php

namespace App\Http\Controllers\Client;

use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{ClientPreference, VendorSection, VendorSectionHeadingTranslation, Vendor, VendorSectionTranslation};

class VendorSectionController extends BaseController {
    use ApiResponser;
    public function store(Request $request){
       
         try {

            $this->validate($request, [
              'heading' => 'required|string|max:60',
            ],['heading' => 'The heading field is required.']);
            
            DB::beginTransaction();
            $vendor_section = new VendorSection();
            $vendor_section->slug = Str::slug($request->heading[0], '-');
            $vendor_section->vendor_id = $request->vendor_id;
            $vendor_section->save();

            $vendor_section_heading_translation             = new VendorSectionHeadingTranslation();
            $vendor_section_heading_translation->heading    = $request->heading;
            $vendor_section_heading_translation->language_id      = $request->language_id;
            $vendor_section_heading_translation->vendor_section_id = $vendor_section->id;
            $vendor_section_heading_translation->save();

            
           
            foreach($request->title as $key=>$value){
                $sectionData = [
                    'vendor_section_id' => $vendor_section->id,
                    'title'             => $value,
                    'description'       => $request->description[$key] ?? '',
                    'language_id'       => $request->language_id,
                ];
                $vendor_section_translation = VendorSectionTranslation::updateOrCreate( $sectionData );
                // $vendor_section_translation                    = new VendorSectionTranslation();
                // $vendor_section_translation->title             = $value;
                // $vendor_section_translation->description       = $request->description[$key] ?? '';
                // $vendor_section_translation->language_id       = $request->language_id;
                // $vendor_section_translation->vendor_section_id = $vendor_section->id;
                // $vendor_section_translation->save();
            }
            $Vendor_section = VendorSection::with('headingTranslation','SectionTranslation')->where('id',$vendor_section->id)->first();
            //pr( $Vendor_section);
            
            DB::commit();
            return $this->successResponse($Vendor_section, 'Vendor Section Added Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }
    public function update(Request $request){
       
        try {

           $this->validate($request, [
             'heading' => 'required|string|max:60',
             'section_id' => 'required|exists:vendor_sections,id'
           ],['heading' => 'The heading field is required.']);
           
           DB::beginTransaction();

           $vendor_section =VendorSection::where(['vendor_id'=>$request->vendor_id,'id'=>$request->section_id])->first();
           if(!$vendor_section){
            return $this->errorResponse([], __('Section Not Found!'));
           }

           $vendor_section_heading_translation             = VendorSectionHeadingTranslation::where([
                                                                                    "language_id"=>$request->language_id,      
                                                                                    "vendor_section_id"=>$vendor_section->id 
                                                                                    ])
                                                                                    ->first();
                                                                          
           if( !$vendor_section_heading_translation )
           $vendor_section_heading_translation             =  new VendorSectionHeadingTranslation();

           $vendor_section_heading_translation->heading               = $request->heading;
           $vendor_section_heading_translation->vendor_section_id     = $vendor_section->id;
           $vendor_section_heading_translation->language_id           = $request->language_id;
           $vendor_section_heading_translation->save();
          
           //DELETE section which not coming 
           $section_old_ids = $request->section_old_ids;
        
            $section_old_ids = array_values(array_diff($section_old_ids, array('')));
           if($request->section_old_ids)
           VendorSectionTranslation::whereNotIn('id',$section_old_ids)
                                    ->where(['vendor_section_id' => $vendor_section->id,
                                            'language_id'=>$request->language_id
                                    ])->delete();
           
          
           foreach($request->title as $key=>$value){
                if($value){
                    $sectionData = [
                        'vendor_section_id' => $vendor_section->id,
                        'title'             => $value,
                        'description'       => $request->description[$key] ?? '',
                        'language_id'       => $request->language_id,
                    ];  
               
                    $vendor_section_translation = VendorSectionTranslation::updateOrCreate(
                        ['vendor_section_id' => $vendor_section->id ,'id'=> $request->section_old_ids[$key] ],
                     $sectionData );
                }
           }
           DB::commit();
          
           return $this->successResponse($vendor_section, __('Vendor Section Update Successfully.'));
       } catch (Exception $e) {
           DB::rollback();
           return $this->errorResponse([], $e->getMessage());
       }
   }
    public function show(Request $request, $domain = '', $id){
        $language_id = $request->language_id;
        $VendorSection =  VendorSection::with(array('headingTranslation' => function($query) use($language_id) {
            $query->where('language_id', $language_id);
        },'SectionTranslation' => function($query) use($language_id) {
            $query->where('language_id', $language_id);
        }
        ))->where('id', $id)->first();
        //pr($page->toArray());
        return $this->successResponse($VendorSection);
    }

    public function destroy(Request $request, $domain = "" ,$section_id)
    {
        VendorSection::where('id',$section_id)->delete();
        VendorSectionHeadingTranslation::where('vendor_section_id',$section_id)->delete();
        VendorSectionTranslation::where('vendor_section_id',$section_id)->delete();
        return redirect()->back()->with('success', __('Section Deleted successfully!'));
    }
}