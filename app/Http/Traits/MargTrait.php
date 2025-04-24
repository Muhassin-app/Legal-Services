<?php
namespace App\Http\Traits;

use DB;
use Auth;
use HttpRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\{Client as CP, Order, ProductAddon, ProductAttribute, ProductCelebrity, ProductCrossSell, ProductRelated, ProductTag, ProductUpSell, SubscriptionInvoicesVendor, VendorMargConfig};
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\{User, UserVendor, VendorAdditionalInfo, VendorMultiBanner,WebStylingOption, Product, ClientLanguage, ProductCategory, ProductVariant, ProductTranslation, MargProduct};


trait MargTrait{

    /**
     * getMultiBanner
     *
     * @param  mixed $vendor_id
     * @return void
     */

    public function getData($crulUrl, $payload){
        
        $ch = curl_init( $crulUrl );
        $payload = json_encode( $payload);
        curl_setopt( $ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
        curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
        curl_setopt($ch, CURLOPT_TIMEOUT, 20); 
        $response = curl_exec($ch);

        $err = curl_error($ch);
        curl_close($ch);
        $encrypted = $response;
        return $encrypted;
        // dd($encrypted);
    }


    public function addProduct($request,$vendor_id = null)
	{
        try{
            DB::beginTransaction();	
          
            $is_exist = Product::where(['sku' => $request->code.'_'.$vendor_id, 'vendor_id' => $vendor_id])->withTrashed()->first();
            // $mega_vendor_id = $is_exist->vendor->mega_vendor_id;
            $vendor_id = $vendor_id ?? 8;

            if(isset($request->ProductCode) && isset($request->name) && is_null($is_exist)){
                // $url_slug = $this->validateSlug($request->name);
                // $request->catcode = 5;

                // $product = new Product();
                // $product->sku = $request->code.'_'.$vendor_id;      // $request->sku;
                // $product->url_slug = $url_slug.'_'.$vendor_id;             // $request->url_slug;
                // $product->title = $request->name;           // $request->product_name;        
                // $product->category_id = $request->catcode;  // $request->category_id;
                // $product->type_id = 1;
                // $product->is_live = 1;
                // $product->vendor_id = $vendor_id;                    //$request->vendor_id;
                // $client_lang = ClientLanguage::where('is_primary', 1)->first();
                // if (!$client_lang) {
                //     $client_lang = ClientLanguage::where('is_active', 1)->first();
                // }
                // $product->save();
                
                // if ($product->id > 0) {
                //         $marg_product  =  new MargProduct();
                //         $marg_product->product_id   =       $product->id;
                //         $marg_product->vendor_id    =       $vendor_id;
                //         $marg_product->rid          =       $request->rid;
                //         $marg_product->catcode      =       $request->catcode;               
                //         $marg_product->code         =       $request->code;               
                //         $marg_product->name         =       $request->name;               
                //         $marg_product->stock        =       $request->stock;
                //         $marg_product->remark       =       $request->remark;
                //         $marg_product->company      =       $request->company;
                //         $marg_product->shopcode     =       $request->shopcode;
                //         $marg_product->MRP          =       $request->MRP;
                //         $marg_product->Rate         =       $request->Rate;
                //         $marg_product->Deal         =       $request->Deal;
                //         $marg_product->Free         =       $request->Free;
                //         $marg_product->PRate        =       $request->PRate;
                //         $marg_product->Is_Deleted   =       $request->Is_Deleted;
                //         $marg_product->curbatch     =       $request->curbatch;
                //         $marg_product->exp          =       $request->exp;
                //         $marg_product->gcode        =       $request->gcode;
                //         $marg_product->MargCode     =       $request->MargCode;
                //         $marg_product->Conversion   =       $request->Conversion;
                //         $marg_product->Salt         =       $request->Salt;
                //         $marg_product->ENCODE       =       $request->ENCODE;
                //         $marg_product->remarks      =       $request->remarks;
                //         $marg_product->Gcode6       =       $request->Gcode6;
                //         $marg_product->ProductCode  =       $request->ProductCode;
                //         $marg_product->save();

                //         $datatrans[] = [
                //             'title' => $request->name??null, // $request->product_name??null,
                //             'body_html' => '',
                //             'meta_title' => '',
                //             'meta_keyword' => '',
                //             'meta_description' => '',
                //             'product_id' => $product->id,
                //             'language_id' => $client_lang->language_id
                //         ];
        
                //         $product_category = new ProductCategory();
                //         $product_category->product_id = $product->id;
                //         $product_category->category_id = $request->catcode; // $request->category_id;
                //         $product_category->save();

                //         $productVariant = ProductVariant::where(['sku' => $request->code.'_'.$vendor_id])->first();

                //         if (is_null($productVariant)) {
                //             $proVariant = new ProductVariant();
                //             $proVariant->sku = $request->code.'_'.$vendor_id; // $request->sku;
                //             $proVariant->product_id = $product->id;            
                //             $proVariant->price = $request->MRP;            
                //             $proVariant->quantity = $request->stock;            
                //             $proVariant->barcode = $this->generateBarcodeNumber();
                //             $proVariant->save();
                //         }else{
                //             $productVariant->price = $request->MRP;            
                //             $productVariant->quantity = $request->stock;            
                //             $productVariant->barcode = $this->generateBarcodeNumber();
                //             $productVariant->save();
                //         }
                        

                //         ProductTranslation::insert($datatrans);

                //         if(@$request->Is_Deleted)
                //         {
                //             $product->delete();
                //             isset($proVariant) ? $proVariant->delete() : '';
                //         }

                // }
            }else{
                $log = [
                    'stock'=>$request->stock,
                    'name'=>$request->name
                ];
                //Update Stock Details

                $request->code = $request->code.'_'.$vendor_id; 
                $this->updateProduct($request,$is_exist);
            }

            DB::commit();

		} catch (Exception $e) {
            DB::rollback();
			return $e->getMessage();
		}
		
	}		

    function updateProduct($request,$product)
	{
        
		try{
			// DB::beginTransaction();
            $url_slug = $this->validateSlug($request->name);
            $product = Product::select('id','sku','url_slug','title')->withTrashed()->findOrFail($product->id);           

            if($product->id){
                $product->sku = $request->code;      // $request->sku;
                $product->url_slug = $url_slug;             // $request->url_slug;
                $product->title = $request->name;           // $request->product_name;             
                $client_lang = ClientLanguage::select('is_primary','is_active','language_id')->where('is_primary', 1)->first();
                if (!$client_lang) {
                    $client_lang = ClientLanguage::select('is_primary','is_active','language_id')->where('is_active', 1)->first();
                }
                $product->save();

            }
            
            if ($product->id > 0)
            {
                    $marg_product  =  MargProduct::select('id','product_id','rid','code','name','stock','MRP')->whereCode($request->code)->first();
                    if($marg_product){
                        $marg_product->product_id   =       $product->id;
                        $marg_product->rid          =       $request->rid;           
                        $marg_product->code         =       $request->code;               
                        $marg_product->name         =       $request->name;               
                        $marg_product->stock        =       $request->stock;
                        $marg_product->MRP          =       $request->MRP;
                        $marg_product->save();
                    }

                $proVariant = ProductVariant::select('id','price','quantity')->where('sku', $request->code)->first();
                if(isset($request->ProductCode) && isset($request->name) && !is_null($proVariant)){

                    $proVariant->price = $request->MRP;            
                    $proVariant->quantity = $request->stock;            
                    $proVariant->save();
                }

                $datatrans = [
                    'title' => $request->name??null, // $request->product_name??null,
                    'body_html' => '',
                    'meta_title' => '',
                    'meta_keyword' => '',
                    'meta_description' => '',
                    'product_id' => $product->id,
                    'language_id' => $client_lang->language_id
                ];

                ProductTranslation::UpdateOrCreate(['product_id' => $product->id,'language_id' => $client_lang->language_id],$datatrans);


                if(@$request->Is_Deleted)
                {
                    $product->delete();
                    isset($proVariant) ? $proVariant->delete() : '';
                }
                
            }
			// DB::commit();
			
			// return $this->successResponse([], 'Product Updated successfully!', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

    function validateSlug($slug) {
        // Convert the slug to lowercase and remove non-alphanumeric characters
        $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($slug));
    
        // Remove leading and trailing hyphens
        $slug = trim($slug, '-');
    
        // Validate the slug format
        if (preg_match('/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug)) {
            return $slug;
        }
    
        return false; // Slug validation failed
    }

    public function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (\DB::table('product_variants')->select('id','barcode')->where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }


	 // Insert order api url-  https://corporate.margerp.com/api/eOnlineData/InsertOrderDetail
    // Parameters-   { "OrderID":"", "OrderNo": "78789", "CustomerID": "5929958", "MargID": "339157", "Type": "S", "Sid": "194130", "ProductCode": "1000004", "Quantity": "2", "Free": "0,0", "Lat": "", "Lng": "", "Address": "", "GpsID": "0", "UserType": "1", "Points": "0.00", "Discounts": "0", "Transport": "", "Delivery": "", "Bankname": "", "BankAdd1": "", "BankAdd2": "", "shipname": "", "shipAdd1": "", "shipAdd2": "", "shipAdd3": "", "paymentmode": "1", "paymentmodeAmount": "0", "payment_remarks": "", "order_remarks": "","CustName":"ramU" ,"CustMobile": "9289757820", "CompanyCode": "RakeshApi2", "OrderFrom": "RakeshApi2" }

	public function makeInsertOrderMargApi($order)
	{
        
    try{
            
        $productCode = [];
		$productQuantity = [];
		$rid = [];

		if(empty($order))
		{
			return false;
		}else{
			foreach($order->products as $product)
			{
				$productCode[] = $product->sku;
				$productQuantity[] = $product->quantity;
			}
            $rid  = MargProduct::first();
		} 
		  
        // $hub_key = @getAdditionalPreference(['marg_access_token','is_marg_enable','marg_decrypt_key', 'marg_company_code','marg_company_url']);
        $hub_key = VendorMargConfig::where('vendor_id',$order->ordervendor->vendor_id)->first()->toArray();


        if($hub_key && $hub_key['is_marg_enable'] == 1){
            $decryptionKey  = $hub_key['marg_decrypt_key'];
            $MargID  = $hub_key['marg_access_token'];
            $CompanyCode  = $hub_key['marg_company_code'];
            $url  = $hub_key['marg_company_url'];
            $detail         = [];
            $MargMST2017 = $url."/api/eOnlineData/InsertOrderDetail";
            $detail = ["OrderID"=>"", "OrderNo"=> $order->order_number, "CustomerID"=> '7532253', "MargID"=> $MargID, "Type"=> "S", "Sid"=> "194130", "ProductCode"=> implode(',',$productCode), "Quantity"=>  implode(',',$productQuantity), "Free"=> "0,0", "Lat"=> "", "Lng"=> "", "Address"=> "", "GpsID"=> "0", "UserType"=> "1", "Points"=> "0.00", "Discounts"=> "0", "Transport"=> "", "Delivery"=> "", "Bankname"=> "", "BankAdd1"=> "", "BankAdd2"=> "", "shipname"=> "", "shipAdd1"=> "", "shipAdd2"=> "", "shipAdd3"=> "", "paymentmode"=> "1", "paymentmodeAmount"=> "0", "payment_remarks"=> "", "order_remarks"=> "","CustName"=>"ramU" ,"CustMobile"=> "9289757820", "CompanyCode"=> $CompanyCode, "OrderFrom"=> $CompanyCode];


             // Get the encrypted data from the request
            $encryptedData = $this->getData($MargMST2017, $detail);

            $encryptedData = json_decode($encryptedData);

            if(isset($encryptedData) && !isset($encryptedData->Message))
            {
				$updateOrder = Order::findOrFail($order->id);
				$updateOrder->marg_status = $encryptedData??1;
				$updateOrder->marg_max_attempt =$updateOrder->marg_max_attempt + 1;
				$updateOrder->save();
                session()->flash('success', 'Order synced successfully!');
                return true;
                
            }else{
                $updateOrder = Order::findOrFail($order->id);
				$updateOrder->marg_max_attempt =$updateOrder->marg_max_attempt + 1;
				$updateOrder->save();
                session()->flash('success',$encryptedData->Message??'Somthing Went Wrong!');
                return $encryptedData->Message;
            }
            return true;

        }else{
            return false;
        }
        }catch(\Exception $e)
        {
            \Log::info($e->getMessage());
            return true;
        }
		
	}



}
