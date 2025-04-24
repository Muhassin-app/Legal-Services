<?php

namespace App\Http\Controllers\Client;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\VendorRegistrationDocument;
use App\Http\Controllers\Client\BaseController;
use App\Models\VendorRegistrationDocumentTranslation;
use App\Models\VendorRegistrationSelectOption;
use App\Models\VendorRegistrationSelectOptionTranslations;

class VendorRegistrationDocumentController extends BaseController{
    use ApiResponser;
    public function store(Request $request){

        try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
              'file_type' => 'required',
            ],['name.0' => 'The default language name field is required.']);
            if($request->file_type=="Selecter"){
                $this->validate($request, [
                    'option_name.0.0' => 'required|string|max:60',
                  ],['option_name.0.0' => 'The default Option name field is required.']);
            }
            DB::beginTransaction();
            $vendor_registration_document = new VendorRegistrationDocument();
            $vendor_registration_document->file_type = $request->file_type;
            $vendor_registration_document->is_required = $request->is_required;
            $vendor_registration_document->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $VendorRegistrationDocumentTranslation = new VendorRegistrationDocumentTranslation();
                    $VendorRegistrationDocumentTranslation->name = $name;
                    $VendorRegistrationDocumentTranslation->slug = Str::slug($name, '-');
                    $VendorRegistrationDocumentTranslation->language_id = $language_id[$k];
                    $VendorRegistrationDocumentTranslation->vendor_registration_document_id = $vendor_registration_document->id;
                    $VendorRegistrationDocumentTranslation->save();
                }
            }
            if($request->has('option_name')){
                foreach($request->option_name as $key =>$value){

                    if(isset($value[0]) && !empty($value[0])){
                        $option  = new VendorRegistrationSelectOption();
                        $option->vendor_registration_documents_id = $vendor_registration_document->id;
                        $option->save();

                        foreach($request->language_id as $lang_key =>$lang_value){
                            if(isset($value[$lang_key]) && !empty($value[$lang_key])){
                                $optionTrabslation  = new VendorRegistrationSelectOptionTranslations();
                                $optionTrabslation->vendor_registration_select_option_id =$option->id ;
                                $optionTrabslation->language_id = $lang_value;
                                $optionTrabslation->name =$value[$lang_key] ;
                                $optionTrabslation->save();
                            }
                        }
                    }


                }
            }

            DB::commit();
            return $this->successResponse($vendor_registration_document, __('Vendor Registration Document Added Successfully.'));
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        try {
            $language_id = $request->language_id;
            $vendor_registration_document = VendorRegistrationDocument::with(['translations'])->where(['id' => $request->vendor_registration_document_id])->firstOrFail();
            if($vendor_registration_document->file_type == 'selector'){
                $vendor_registration_document->options = VendorRegistrationSelectOption::with(['translations'])
                                                            ->where(['vendor_registration_documents_id' => $request->vendor_registration_document_id])
                                                            ->get();
            }
            return $this->successResponse($vendor_registration_document, '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, VendorRegistrationDocument $vendorRegistrationDocument){
         try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
              'file_type' => 'required',
            ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();
            $vendor_registration_document_id = $request->vendor_registration_document_id;
            $vendor_registration_document = VendorRegistrationDocument::where('id', $vendor_registration_document_id)->first();
            $vendor_registration_document->file_type = $request->file_type;
            $vendor_registration_document->is_required = $request->is_required;
            $vendor_registration_document->save();
            $language_id = $request->language_id;
            VendorRegistrationDocumentTranslation::where('vendor_registration_document_id', $vendor_registration_document_id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $VendorRegistrationDocumentTranslation = new VendorRegistrationDocumentTranslation();
                    $VendorRegistrationDocumentTranslation->name = $name;
                    $VendorRegistrationDocumentTranslation->slug = Str::slug($name, '-');
                    $VendorRegistrationDocumentTranslation->language_id = $language_id[$k];
                    $VendorRegistrationDocumentTranslation->vendor_registration_document_id = $vendor_registration_document->id;
                    $VendorRegistrationDocumentTranslation->save();
                }
            }
            $delete_option = [];

            if($request->has('option_name')){
                foreach($request->option_name as $key =>$value){
                    if(isset($value[0]) && !empty($value[0])){
                        $data = [
                            'vendor_registration_documents_id'       =>$vendor_registration_document->id
                        ];
                        $option = VendorRegistrationSelectOption::updateOrCreate(
                            ['id' => $request->option_id[$key][0] ],
                            $data
                        );
                        $delete_option[] =$option->id;
                        foreach($request->language_id as $lang_key =>$lang_value){
                            if(isset($value[$lang_key]) && !empty($value[$lang_key])){
                                $translationData = [
                                    'name' => $value[$lang_key]
                                ];
                                $optionTrabslation = VendorRegistrationSelectOptionTranslations::updateOrCreate(
                                    ['vendor_registration_select_option_id' => $option->id,'language_id'=>  $lang_value],
                                    $translationData
                                );
                            }
                        }
                    }
                }
            }

            VendorRegistrationSelectOption::whereNotIn('id',$delete_option)
                                            ->where('vendor_registration_documents_id', $vendor_registration_document_id)
                                            ->delete();

            DB::commit();
            return $this->successResponse($vendor_registration_document, 'Vendor Registration Document Updated Successfully.');
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request){
        try {
            VendorRegistrationDocument::where('id', $request->vendor_registration_document_id)->delete();
            VendorRegistrationDocumentTranslation::where('vendor_registration_document_id', $request->vendor_registration_document_id)->delete();
            return $this->successResponse([], 'Vendor Registration Document Deleted Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
}
