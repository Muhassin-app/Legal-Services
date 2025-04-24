<?php

namespace App\Http\Controllers\Client;
use DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\UserRegistrationDocuments;
use App\Http\Controllers\Client\BaseController;
use App\Models\UserRegistrationDocumentTranslation;
use App\Models\UserRegistrationSelectOption;
use App\Models\UserRegistrationSelectOptionTranslations;

class UserRegistrationDocumentController extends BaseController{
    use ApiResponser;
    public function store(Request $request){

        try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
              'file_type' => 'required',
            ],['name.0' => 'The default language name field is required.']);
            
            DB::beginTransaction();
            $user_registration_document = new UserRegistrationDocuments();
            $user_registration_document->file_type = $request->file_type;
            $user_registration_document->is_required = $request->is_required;
            $user_registration_document->save();
            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if($name){
                    $data= [
                            'name' => $name,
                            'slug' => Str::slug($name, '-'),
                            'language_id' =>$language_id[$k],
                            'user_registration_document_id' =>$user_registration_document->id
                        ];
                        $Loction = UserRegistrationDocumentTranslation::updateOrCreate(
                        ['slug'=>Str::slug($name, '-'),'language_id' =>$language_id[$k],'user_registration_document_id' =>$user_registration_document->id ],
                            $data
                        );
                }
            }

            if($request->has('option_name')){
                foreach($request->option_name as $key =>$value){

                    if(isset($value[0]) && !empty($value[0])){
                        $option  = new UserRegistrationSelectOption();
                        $option->user_registration_documents_id = $user_registration_document->id;
                        $option->save();

                        foreach($request->language_id as $lang_key =>$lang_value){
                            if(isset($value[$lang_key]) && !empty($value[$lang_key])){
                                $optionTrabslation  = new UserRegistrationSelectOptionTranslations();
                                $optionTrabslation->user_registration_select_option_id =$option->id ;
                                $optionTrabslation->language_id = $lang_value;
                                $optionTrabslation->name =$value[$lang_key] ;
                                $optionTrabslation->save();
                            }
                        }
                    }

                }
            }
           
            DB::commit();
            return $this->successResponse($user_registration_document, 'User Registration Document Added Successfully.');
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
            $user_registration_document = UserRegistrationDocuments::with(['translations'])->where(['id' => $request->user_registration_document_id])->firstOrFail();
            if($user_registration_document->file_type == 'selector'){
                $user_registration_document->options = UserRegistrationSelectOption::with(['translations'])
                            ->where(['user_registration_documents_id' => $request->user_registration_document_id])
                            ->get();
            }
            return $this->successResponse($user_registration_document, '');
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
    public function update(Request $request, UserRegistrationDocuments $userRegistrationDocument){
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
            $user_registration_document_id = $request->user_registration_document_id;
            $user_registration_document = UserRegistrationDocuments::where('id', $user_registration_document_id)->first();
            $user_registration_document->file_type = $request->file_type;
            $user_registration_document->is_required = $request->is_required;
            $user_registration_document->save();
            $language_id = $request->language_id;
            UserRegistrationDocumentTranslation::where('user_registration_document_id', $user_registration_document_id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $data= [
                        'name' => $name,
                        'slug' => Str::slug($name, '-'),
                        'language_id' =>$language_id[$k],
                        'user_registration_document_id' =>$user_registration_document->id
                    ];
                    $Loction = UserRegistrationDocumentTranslation::updateOrCreate(
                    ['slug'=>Str::slug($name, '-'),'language_id' =>$language_id[$k],'user_registration_document_id' =>$user_registration_document->id ],
                        $data
                    );
                }
            }

            $delete_option = [];

            if($request->has('option_name')){
                foreach($request->option_name as $key =>$value){
                    if(isset($value[0]) && !empty($value[0])){
                        $data = [
                            'user_registration_documents_id'       =>$user_registration_document->id
                        ];
                        $option = UserRegistrationSelectOption::updateOrCreate(
                            ['id' => $request->option_id[$key][0] ],
                            $data
                        );
                        $delete_option[] =$option->id;
                        foreach($request->language_id as $lang_key =>$lang_value){
                            if(isset($value[$lang_key]) && !empty($value[$lang_key])){
                                $translationData = [
                                    'name' => $value[$lang_key]
                                ];
                                $optionTrabslation = UserRegistrationSelectOptionTranslations::updateOrCreate(
                                    ['user_registration_select_option_id' => $option->id,'language_id'=>  $lang_value],
                                    $translationData
                                );
                            }
                        }
                    }
                }
            }

            UserRegistrationSelectOption::whereNotIn('id',$delete_option)
                                            ->where('user_registration_documents_id', $user_registration_document_id)
                                            ->delete();

            DB::commit();
            return $this->successResponse($user_registration_document, 'User Registration Document Updated Successfully.');
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
            UserRegistrationDocuments::where('id', $request->user_registration_document_id)->delete();
            UserRegistrationDocumentTranslation::where('user_registration_document_id', $request->user_registration_document_id)->delete();
            return $this->successResponse([], 'User Registration Document Deleted Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
}
