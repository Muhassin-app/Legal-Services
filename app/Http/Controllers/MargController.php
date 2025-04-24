<?php

namespace App\Http\Controllers;
use Illuminate\Support\Collection;
// use App\Libraries\MyDLLWrapper;
use App\Libraries\DecryptLogic;
use GuzzleHttp\Client as GCLIENT;
use Illuminate\Http\Request;
use App\Http\Traits\MargTrait;
use App\Models\Client;
use App\Models\ClientLanguage;
use App\Models\ClientPreferenceAdditional;
use App\Models\MargProduct;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductTranslation;
use App\Models\ProductVariant;
use App\Models\VendorMargConfig;
use Illuminate\Support\Facades\Artisan;

class MargController extends Controller
{
    use MargTrait;
    protected $DecryptLogic;

    public function __construct(DecryptLogic $DecryptLogic)
    {
        $this->DecryptLogic = $DecryptLogic;
    }

    public function margcmd()
    {
        Artisan::call('fetch:margdata');
    }
    
    public function syncmarg()
    {
        $hub_key = @getAdditionalPreference(['marg_access_token','is_marg_enable','marg_decrypt_key', 'marg_company_code','marg_company_url']);

        if($hub_key['is_marg_enable'] == 1){
            $decryptionKey  = $hub_key['marg_decrypt_key'];
            $MargID  = $hub_key['marg_access_token'];
            $CompanyCode  = $hub_key['marg_company_code'];
            $margDateTime = $hub_key['marg_date_time']??date('Y-m-d H:i:s');
            $url  = $hub_key['marg_company_url'];

            $detail         = [];
            $MargMST2017 = $url."/api/eOnlineData/MargMST2017";
            $reqData = ["CompanyCode" => $CompanyCode,"MargID" => $MargID,"Datetime" =>$margDateTime, "index" => 0];
        }else{
            return false;
        }

        $client = Client::first();
        ClientPreferenceAdditional::updateOrCreate(
            ['key_name' => 'marg_date_time', 'client_code' => $client->code],
            ['key_name' => 'marg_date_time', 'key_value' => date('Y-m-d H:i:s'),'client_code' => $client->code,'client_id'=> $client->id]);
         
        // Get the encrypted data from the request
        $encryptedData = $this->getData($MargMST2017, $reqData);

        // Decrypt the data using the DLL wrapper
        $decryptedData = $this->DecryptLogic->Decrypt($encryptedData, $decryptionKey);
        $collectionData = collect( json_decode($decryptedData));
            //    dd($collectionData["Details"]->pro_N);
        if(!empty($collectionData["Details"]->pro_N)){

        // ---------------------- With Dispatch ---------------------
            // $chunck = array_chunk($collectionData["Details"]->pro_N,100);
            // foreach($chunck as $data){
            //     dispatch(new \App\Jobs\SyncFromMarg($data))->onQueue('sync_data_from_marg');
            // }
        // ---------------------- Without Dispatch ------------------
            foreach($collectionData["Details"]->pro_N as $key => $product){
               $detail = $this->addProduct($product);
            }
        }

        $time = '';
        $time = convertDateTimeInClientTimeZone(date('Y-m-d H:i:s'), 'd-m-Y h:i:s');
        // // Return the decrypted data in the API response
        return response()->json(['time' =>__('Last Sync Date & Time : ').$time]);

        // $resp =  $this->makeInsertOrderMargApi();
        // dd($resp);
    }

    public function syncmargVendor($domain,$vendor_id)
    {

        $vendor_marg_config = VendorMargConfig::select('id','vendor_id','is_marg_enable','marg_decrypt_key','marg_access_token','marg_company_code','marg_date_time','marg_company_url')->where('vendor_id',$vendor_id)->first();
        
        if($vendor_marg_config->is_marg_enable == 1){
            $decryptionKey  = $vendor_marg_config->marg_decrypt_key;
            $MargID  = $vendor_marg_config->marg_access_token;
            $CompanyCode  = $vendor_marg_config->marg_company_code;
            $margDateTime = $vendor_marg_config->marg_date_time??'';
            $url  = $vendor_marg_config->marg_company_url;

            $detail         = [];
            $MargMST2017 = $url."/api/eOnlineData/MargMST2017";
            $reqData = ["CompanyCode" => $CompanyCode,"MargID" => $MargID,"Datetime" =>$margDateTime, "index" => 0];
        }else{
            return false;
        }

        // Get the encrypted data from the request
        $encryptedData = $this->getData($MargMST2017, $reqData);
        // Decrypt the data using the DLL wrapper
        $decryptedData = $this->DecryptLogic->Decrypt($encryptedData,$decryptionKey);
        $collectionData = collect( json_decode($decryptedData));
            //    dd($collectionData["Details"]->pro_N);

        $vendor_marg_config->update([
            'marg_last_date_time' => date('Y-m-d H:i:s')
        ]);
        
        if(!empty($collectionData["Details"]->pro_N)){

        // ---------------------- With Dispatch ---------------------
            // $chunck = array_chunk($collectionData["Details"]->pro_N,100);
            // foreach($chunck as $data){
            //     dispatch(new \App\Jobs\SyncFromMarg($data))->onQueue('sync_data_from_marg');
            // }
        // ---------------------- Without Dispatch ------------------
                    
            // \Log::info(count($collectionData["Details"]->pro_N));
            
            // $chunks = array_chunk($collectionData["Details"]->pro_N, 1000);

            $client_lang = \DB::table('client_languages')->select('language_id','is_primary')->where('is_primary', 1)->first();
            if (!$client_lang) {
                $client_lang = \DB::table('client_languages')->select('language_id','is_active')->where('is_active', 1)->first();
            }    
            

            collect($collectionData["Details"]->pro_N)->chunk(2000)->each(function($produts) use ($vendor_id,$client_lang){
                $addMargProductsArray = [];
                $productCategoryArray = [];
                $productTrans = [];
                $productVariantArray = [];
    
                $productTransUpdate = [];
                $productVariantArrayUpdate = [];
                $addMargProductsArrayUpdate = [];
                $productUpdateArray = [];

                foreach($produts as $key => $product){
                    $request = $product;
                    try{
                        \DB::beginTransaction();	
                
                        $is_exist = \DB::table('products')->select('id','sku','vendor_id')->where(['sku' => $request->code.'_'.$vendor_id, 'vendor_id' => $vendor_id])->where(function($q){
                            $q->where('deleted_at', null)->orWhere('deleted_at','!=', null);
                        })->first();

                        $vendor_id = $vendor_id ?? 8;

                        $url_slug = $this->validateSlug($request->name);
            
                        if(isset($request->ProductCode) && isset($request->name) && is_null($is_exist)){
                            $request->catcode = 5;

                            $product = \DB::table('products')->insert([
                                'sku' => $request->code . '_' . $vendor_id,
                                'url_slug' => $url_slug . '_' . $vendor_id,
                                'title' => $request->name,
                                'category_id' => $request->catcode,
                                'type_id' => 1,
                                'is_live' => 1,
                                'vendor_id' => $vendor_id,
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            $product_id = \DB::getPdo()->lastInsertId();
                                                    
                            if ($product_id > 0) {
                                $addMargProductsArray[] = [
                                    "product_id"   =>   $product_id,
                                    "vendor_id"    =>   $vendor_id,
                                    "rid"          =>   $request->rid,
                                    "catcode"      =>   $request->catcode,
                                    "code"         =>   $request->code,
                                    "name"         =>   $request->name,
                                    "stock"        =>   $request->stock,
                                    "remark"       =>   $request->remark,
                                    "company"      =>   $request->company,
                                    "shopcode"     =>   $request->shopcode,
                                    "MRP"          =>   $request->MRP,
                                    "Rate"         =>   $request->Rate,
                                    "Deal"         =>   $request->Deal,
                                    "Free"         =>   $request->Free,
                                    "PRate"        =>   $request->PRate,
                                    "Is_Deleted"   =>   $request->Is_Deleted,
                                    "curbatch"     =>   $request->curbatch,
                                    "exp"          =>   $request->exp,
                                    "gcode"        =>   $request->gcode,
                                    "MargCode"     =>   $request->MargCode,
                                    "Conversion"   =>   $request->Conversion,
                                    "Salt"         =>   $request->Salt,
                                    "ENCODE"       =>   $request->ENCODE,
                                    "remarks"      =>   $request->remarks,
                                    "Gcode6"       =>   $request->Gcode6,
                                    "ProductCode"  =>   $request->ProductCode
                                ];
            
                                $productTrans[] = [
                                    'title' => $request->name??null,
                                    'body_html' => '',
                                    'meta_title' => '',
                                    'meta_keyword' => '',
                                    'meta_description' => '',
                                    'product_id' => $product_id,
                                    'language_id' => $client_lang->language_id
                                ];
                
                                $productCategoryArray[] = [
                                    "product_id" => $product_id,
                                    "category_id" => $request->catcode,
                                ];
                                
                                $productVariantArray[] = [
                                    'sku' => $request->code . '_' . $vendor_id,
                                    'product_id' => $product_id,
                                    'price' => $request->MRP,
                                    'quantity' => $request->stock,
                                    'barcode' => $this->generateBarcodeNumber()
                                ];
            
                            }
                        }else{

                            $productUpdateArray[] =[
                                'id' => $is_exist->id,
                                'sku' => $request->code.'_'.$vendor_id,
                                'url_slug' => $url_slug,
                                'title' => $request->name,
                            ];

                            $ddMargProductsArrayUpdate[] = [
                                'product_id' => $is_exist->id,
                                'rid' => $request->rid,
                                'code' => $request->code,
                                'name' => $request->name,
                                'stock' => $request->stock,
                                'MRP' => $request->MRP,
                            ];

                            $productVariantArrayUpdate[] = [
                                'product_id' => $is_exist->id,
                                'price' => $request->MRP,
                                'quantity' => $request->stock,
                            ];

                            $productTransUpdate[] = [
                                'title' => $request->name??null,
                                'body_html' => '',
                                'meta_title' => '',
                                'meta_keyword' => '',
                                'meta_description' => '',
                                'product_id' => $is_exist->id,
                                'language_id' => $client_lang->language_id
                            ];
                            
                        }

                        \DB::commit();
        
                    } catch (\Exception $e) {
                        \Log::error($e->getMessage() . ' '. $e->getLine());
                        \DB::rollback();

                        return $e->getMessage();
                    }
                }

                \DB::table('product_translations')->insert($productTrans);
                \DB::table('product_variants')->insert($productVariantArray);
                \DB::table('product_categories')->insert($productCategoryArray);
                \DB::table('marg_products')->insert($addMargProductsArray);

                \DB::table('product_translations')->upsert($productTransUpdate,['product_id']);
                \DB::table('product_variants')->upsert($productVariantArrayUpdate,['product_id']);
                \DB::table('marg_products')->upsert($addMargProductsArrayUpdate,['product_id','code']);
                \DB::table('products')->upsert($productUpdateArray,['product_id']);
            });

        // });
        }

        $time = '';
        $time = convertDateTimeInClientTimeZone(date('Y-m-d H:i:s'), 'Y-d-m h:i:s');
        // // Return the decrypted data in the API response
        return response()->json(['time' =>__('Last Sync Date & Time : ').$time]);

        // $resp =  $this->makeInsertOrderMargApi();
        // dd($resp);
    }
}
