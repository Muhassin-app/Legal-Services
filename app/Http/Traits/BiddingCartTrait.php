<?php
namespace App\Http\Traits;

use App\Models\Bid;
use App\Models\Cart;
use App\Models\CartProduct;
use App\Models\ClientCurrency;
use App\Models\BidProduct;
use App\Models\Product;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Front\CartController;
trait biddingCartTrait{

    protected function biddingCart($id)
    {
        $user_id = ' ';
        $cartInfo = ' ';
        $user = Auth::user();
        $is_bid_enable = @getAdditionalPreference(['is_bid_enable'])['is_bid_enable']??0;
        $bid_products = BidProduct::where('bid_id', $id)->with('product.variant')->get();
        $CartController  = new CartController();
        foreach($bid_products as $product) {
            $newRequest = new Request();
            $newRequest->merge(['product_id'=> $product->product_id, 'quantity'=>$product->quantity, 'variant_id'=>$product->product->variant[0]->id, 'vendor_id'=>$product->product->vendor_id,'bid_number'=>(($is_bid_enable)?$id:null),'bid_discount'=>(($is_bid_enable)?$product->bids->discount:null)]);
            $data = $CartController->postAddToCart($newRequest);
        }

        return response()->json(['status' => 'success', 'message' => 'Product Added Successfully!',]);
    }

    public function searchProduct($language_id=1,$keyword='',$vendor_ids=[]){
        
        $products = Product::with(['media', 'vendor','variant'])->join('product_translations as pt', 'pt.product_id', 'products.id')->join('vendors', 'vendors.id', 'products.vendor_id')
        ->select('products.id', 'products.sku', 'products.url_slug', 'pt.title  as dataname', 'pt.body_html', 'pt.meta_title', 'pt.meta_keyword', 'pt.meta_description', 'products.vendor_id', 'vendors.slug as vendor_slug')
        ->where('pt.language_id', $language_id);
        if($keyword){
            $products = $products->where(function ($q) use ($keyword) {
                $q->where('products.sku', ' LIKE', '%' . $keyword . '%')->orWhere('products.url_slug', 'LIKE', '%' . $keyword . '%')->orWhere('pt.title', 'LIKE', '%' . $keyword . '%');
            });
        }
        $products = $products->where('products.is_live', 1);
        $products = $products->whereIn('vendor_id', $vendor_ids);
        $products = $products->whereNull('deleted_at')->groupBy('products.id')->get();

        $product_results = [];
        foreach ($products as $product) {
            $redirect_url = route('productDetail', [$product->vendor_slug, $product->url_slug]);
            $image_url = $product->media->first() ? $product->media->first()->image->path['proxy_url'] . '80/80' . $product->media->first()->image->path['image_path'] : '';
            $product_results[] = ['id' => $product->id, 'name' => $product->dataname , 'price' =>decimal_format($product->variant[0]->price),'variant_id'=>$product->variants[0]->id,'variant'=>$product->variants];
        }
        $response =[];
        if (@$product_results) {
            $response[] = ['title' => '', 'result' => $product_results];
        }
        return $response;
    }
}
