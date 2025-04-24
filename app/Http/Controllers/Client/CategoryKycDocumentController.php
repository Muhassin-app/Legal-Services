<?php

namespace App\Http\Controllers\Client;
use DB;
use Session;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\CategoryKycDocuments;
use App\Http\Controllers\Client\BaseController;
use App\Models\{CategoryKycDocumentTranslation,ClientPreference,Category,CategoryKycDocumentMapping};


class CategoryKycDocumentController extends BaseController{
    use ApiResponser;
    public function store(Request $request){
       
        try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
              'file_type' => 'required',
              'category_id' => 'required',
            ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();
            $category_kyc_document = new CategoryKycDocuments();
            $category_kyc_document->file_type = $request->file_type;
            $category_kyc_document->is_required = $request->is_required;
            $category_kyc_document->save();
            // maping 
            if ($request->category_id != 1 && $request->has('category_id') && count($request->category_id)>0){
                foreach($request->category_id as $category_id){
                    $category_maping = new CategoryKycDocumentMapping();
                    $category_maping->category_kyc_document_id =  $category_kyc_document->id;
                    $category_maping->category_id =$category_id ;
                    $category_maping->save();
                }
            }
           

            $language_id = $request->language_id;
            foreach ($request->name as $k => $name) {
                if(isset($name)){
                    $data= [
                            'name' => $name,
                            'slug' => Str::slug($name, '-'),
                            'language_id' =>$language_id[$k],
                            'category_kyc_document_id' =>$category_kyc_document->id
                        ];
                        CategoryKycDocumentTranslation::updateOrCreate(
                        ['slug'=>Str::slug($name, '-'),'language_id' =>$language_id[$k],'category_kyc_document_id' =>$category_kyc_document->id ],
                            $data
                        );
                }
            }
           
            DB::commit();
            return $this->successResponse($category_kyc_document, __('User Place Order Document Added Successfully.'));
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
            $category_kyc_document = CategoryKycDocuments::with(['translations'])->where(['id' => $request->category_kyc_document_id])->firstOrFail();
            return $this->successResponse($category_kyc_document, '');
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
    public function update(Request $request, CategoryKycDocuments $userRegistrationDocument){
         try {

            $this->validate($request, [
              'name.0' => 'required|string|max:60',
              'file_type' => 'required',
            ],['name.0' => 'The default language name field is required.']);
            DB::beginTransaction();
            $category_kyc_document_id = $request->category_kyc_document_id;
            $category_kyc_document = CategoryKycDocuments::where('id', $category_kyc_document_id)->first();
            $category_kyc_document->file_type = $request->file_type;
            $category_kyc_document->is_required = $request->is_required;
            $category_kyc_document->save();
            $language_id = $request->language_id;
            // maping 
            CategoryKycDocumentMapping::where('category_kyc_document_id',$category_kyc_document->id)->delete();
            if ($request->category_id != 1 && $request->has('category_id') && count($request->category_id)>0){
                foreach($request->category_id as $category_id){
                    $category_maping = new CategoryKycDocumentMapping();
                    $category_maping->category_kyc_document_id =  $category_kyc_document->id;
                    $category_maping->category_id =$category_id ;
                    $category_maping->save();
                }
            }
            CategoryKycDocumentTranslation::where('category_kyc_document_id', $category_kyc_document->id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $data= [
                        'name' => $name,
                        'slug' => Str::slug($name, '-'),
                        'language_id' =>$language_id[$k],
                        'category_kyc_document_id' =>$category_kyc_document->id
                    ];
                    $Loction = CategoryKycDocumentTranslation::updateOrCreate(
                    ['slug'=>Str::slug($name, '-'),'language_id' =>$language_id[$k],'category_kyc_document_id' =>$category_kyc_document->id ],
                        $data
                    );
                }
            }
            DB::commit();
            return $this->successResponse($category_kyc_document, 'User Place Order Document Updated Successfully.');
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
            CategoryKycDocuments::where('id', $request->category_kyc_document_id)->delete();
            CategoryKycDocumentMapping::where('category_kyc_document_id',$request->category_kyc_document_id)->delete();
            CategoryKycDocumentTranslation::where('category_kyc_document_id', $request->category_kyc_document_id)->delete();
            return $this->successResponse([], 'User Place Order Document Deleted Successfully.');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
    public function getCategory(Request $request){
        $category_kyc_document_id = $request->has('category_kyc_document_id') ? $request->category_kyc_document_id : null;
        
        $celebrity_check = ClientPreference::first()->value('celebrity_check');
       
        $categories = Category::with('translation_one','type')->where('id', '>', '1')->orderBy('parent_id', 'asc')->orderBy('position', 'asc')->where('deleted_at', NULL)->where('status', 1);
        $category_ids = [];
        if( $category_kyc_document_id){
            $category_ids =  CategoryKycDocumentMapping::where('category_kyc_document_id',$category_kyc_document_id)->pluck('category_id')->toArray();
        }
       
        
        if ($celebrity_check == 0)
            $categories = $categories->where('type_id', '!=', 5);   # if celebrity mod off .

        $categories = $categories->get();
        //pr( $categories->toArray());
        $options = [];
        foreach($categories as $key => $category){
            if($category->translation_one){
                $select =  in_array($category->id, $category_ids)  ? "selected" : "";
                $options[] = "<option value='".$category->id."' ".$select.">".$category->translation_one->name."</option>";
            }
            
        }

        return response()->json(['status' => 1, 'message' => 'Categories', 'categories' => $categories, 'options' => $options]);
    }
}
