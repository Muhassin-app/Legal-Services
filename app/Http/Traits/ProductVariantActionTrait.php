<?php
namespace App\Http\Traits;
use App\Models\{ProductVariant};
use Illuminate\Support\Str;
use Auth;
use Session;
use Carbon\Carbon;
use DB;
use Grimzy\LaravelMysqlSpatial\Types\Point;


trait ProductVariantActionTrait{

    
    public function productVariantTypeOption($product_id)
    {
       $data = ProductVariant::where('product_id',$product_id)->get();
       pr($data);
    }
    

 
}
