<?php

namespace App\Http\Controllers\Client;

use DB,Log;
use Session;
use DataTables;
use Validation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Traits\{ApiResponser,OrderTrait};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\{BaseController,ProductController};
use App\Models\{Product,ProductVariant,ProductTranslation,LongTermServiceProductAddons,ProductImage, LongTermServiceProducts, ClientPreference,LongTermServicePeriod,OrderLongTermServiceSchedule};
class LongTermServiceController extends BaseController
{
    use ApiResponser,OrderTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request,$domain = '',$vendor_id)
    {
        //echo $vendor_id;
        $LongTermService = Product::with('primary','LongTermProducts','ServicePeriod','media.image','variant')->where(['vendor_id'=>$vendor_id,'is_long_term_service'=>'1'])->get();
        //pr($LongTermService->toArray());
        return Datatables::of($LongTermService)
        ->addIndexColumn()
        ->addColumn('service_title', function ($LongTermService) {
           
            return $LongTermService->primary ? $LongTermService->primary->title : $LongTermService->slug;
        })
        ->addColumn('service_product_title', function ($LongTermService) {
           
            return $LongTermService->LongTermProducts ? ($LongTermService->LongTermProducts->product ?  ( $LongTermService->LongTermProducts->product->primary ? $LongTermService->LongTermProducts->product->primary->title : $LongTermService->LongTermProducts->product->sku  ) : 'NA'  ) : $LongTermService->slug;
        })
        ->addColumn('service_image', function ($LongTermService)  {
            $image = '';
            if($LongTermService->media->first() && !empty($LongTermService->media->first()->image) ){
                $image_path = $LongTermService->media->first()->image->path['proxy_url'] . '30/30' . $LongTermService->media[0]->image->path['image_path'];
                $image = '<img  class="rounded-circle" src="'. $image_path.'">';
            }
            
            return $image;
        })
        ->addColumn('service_product_quantity', function ($LongTermService)  {
           
            return $LongTermService->LongTermProducts ? $LongTermService->LongTermProducts->quantity : 0;
        })
        ->addColumn('time_period', function ($LongTermService)  {
            $title= '';
            if($LongTermService->ServicePeriod){
                foreach($LongTermService->ServicePeriod->pluck('service_period') as $key=> $period){
                    if($key ==0){
                        $title= config('constants.Period.'.$period);
                    }else{
                        $title= $title.','. config('constants.Period.'.$period);
                    }
                }
                
            }
           
            return $title;
        })
        ->editColumn('price', function ($LongTermService)  {
            return $LongTermService->variant->first() ?  decimal_format($LongTermService->variant->first()->price) : 0;
        })
        ->addColumn('action', function ($LongTermService) use ($request) {
            $edit_url = route('long_term_service.edit', $LongTermService->id);
            $delete_url = route('long_term_service.destroy', $LongTermService->id);
            $action = '<div class="form-ul" style="width: 60px;">
            <div class="inner-div" style="float: left;">
                <a class="action-icon edit_service" data-service_id="'.$LongTermService->id.'"
                    href="'.$edit_url.'"
                    userId="'.$LongTermService->id.'"><i
                        class="mdi mdi-square-edit-outline"></i></a>
            </div>
            <div class="inner-div">
                <a class="action-icon delete_service" data-service_id="'.$LongTermService->id.'"  href="'.$edit_url.'" ><i class="mdi mdi-delete"></i></a>
                
            </div>
        </div>';
            
            
            return $action;
        })
        ->rawColumns(['service_title','service_image','service_title','service_product_title','service_product_quantity','time_period','action'])
        ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        try {
            DB::beginTransaction();

            $this->validate($request, [
                'name.0'                => 'required|string|max:60',
                'sku'                   => 'required|unique:products,sku,'.$request->long_term__service_id,
                'service_product_id'    => 'required',
                'product_quantity'      => 'required',
                'serice_price'          => 'required',
                'service_period'      => 'required',
                'service_product_variant_id' => 'required',
            ],['name.0' =>__('The default language name field is required.')]);

            if($request->has('long_term__service_id') && ($request->long_term__service_id != '') ){
                $LongTermService = Product::where('id', $request->long_term__service_id)->first();
            }else{
                $LongTermService = new Product();
                $LongTermService->sku = $request->sku;
                $LongTermService->url_slug   =  $request->sku ;
            }
            $LongTermService->title                 = !empty($request->name[0]) ? $request->name[0] : $request->sku;
            $LongTermService->category_id           = $request->category_id ?? null;
            $LongTermService->is_long_term_service  = 1;
            $LongTermService->is_live               = 1;
           // $LongTermService->service_period        = $request->service_period;
            $LongTermService->service_duration      = $request->service_duration;
            $LongTermService->vendor_id             = $request->vendor_id;
            $LongTermService->save();
            // upload file
            if ($request->hasFile('file')) {
                $request->merge(['prodId'=>$LongTermService->id,'retunId'=>'1']);
                $ProductController = new ProductController();
                $ReaImage          = $ProductController->images($request);
                if(is_numeric($ReaImage)){
                    $productImageSave = [
                        'product_id' => $LongTermService->id,
                        'media_id' => $ReaImage,
                        'is_default' => 1
                    ];
                    ProductImage::insert($productImageSave);
                }
            }
                     

            $proVariant = ProductVariant::where('product_id',$LongTermService->id)->first() ??  new ProductVariant();
            $proVariant->sku                = $request->sku;
            $proVariant->product_id         = $LongTermService->id;
            $proVariant->title              = $request->sku . '-' .  empty($request->product_name) ? $request->sku : $request->product_name;
            $proVariant->price              = $request->serice_price;
            $proVariant->compare_at_price   = $request->serice_price + 10;
            $proVariant->barcode            = $this->generateBarcodeNumber();
            $proVariant->save();

            $language_id = $request->language_id;
            ProductTranslation::where('product_id', $LongTermService->id)->delete();
            foreach ($request->name as $k => $name) {
                if($name){
                    $LongTermServiceT                    = new ProductTranslation();
                    $LongTermServiceT->title             = $name;
                    $LongTermServiceT->language_id       = $language_id[$k];
                    $LongTermServiceT->product_id        = $LongTermService->id;
                    $LongTermServiceT->save();
                }
            }
          
            $request->merge(['long_term_service_id' => $LongTermService->id]);
            
            /** save service product  period */
            LongTermServicePeriod::saveServicePeriod($request);
             /** save service product */
            $ServiceProductId =  LongTermServiceProducts::saveProducts($request);

            /** save service product addons*/
            $request->merge(['long_term_service_product_id' => $ServiceProductId]);
            LongTermServiceProductAddons::saveAddOn( $request);
            $massage = __('Long Term Service Added Successfully.');
            if($request->has('long_term__service_id') && $request->long_term__service_id)
            $massage = __('Long Term Service Updated Successfully.');
            DB::commit();
            return $this->successResponse($LongTermService, $massage);
          
        } catch (\PDOException $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()]);
        }

    }
    public function save(Request $request)
    {
        # code...
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
 
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request,$domain = '', $id)
    {
      
        try {
            $LongTermService = Product::with('translation','LongTermProducts','media.image','variant','ServicePeriod')->where(['id' => $id])->firstOrFail();
           
            $image = '';
            if(($LongTermService) && $LongTermService->media->first() && !empty($LongTermService->media->first()->image) ){
                $image_path = $LongTermService->media->first()->image->path['proxy_url'] . '100/100' . $LongTermService->media[0]->image->path['image_path'];
                $LongTermService->image =  $image_path;
            }
            $LongTermService->ServicePeriods = [];
            if($LongTermService->ServicePeriod){
                $LongTermService->ServicePeriods = $LongTermService->ServicePeriod->pluck('service_period')->toArray();
            }
          
            return $this->successResponse($LongTermService, '');
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request,$domain = '', $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request,$domain = '', $id)
    {
        try {
           // LongTermService::where('id',$id)->delete();
            $productde = Product::productDelete($id);
            return response()->json(array('success' => true,'message'=>__('Deleted successfully.')));
        } catch (Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
    public function generateBarcodeNumber()
    {
        $random_string = substr(md5(microtime()), 0, 14);
        while (ProductVariant::where('barcode', $random_string)->exists()) {
            $random_string = substr(md5(microtime()), 0, 14);
        }
        return $random_string;
    }
    
    /**
     * updateBooking
     *
     * @param  mixed $request
     * @return void
     * update long term order booking  update 
     */
    public function updateBooking(Request $request){
        try {
           
            $res = $this->updateLongTermBooking($request);
            return response()->json(array('success' => true,'message'=>__('Long term booking successfully completed.')));
        } catch (Exception $e) {
             return $this->errorResponse([], $e->getMessage());
        }
    }
}
