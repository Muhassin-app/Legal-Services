<?php

namespace App\Jobs;

use DB,Log;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Http\Controllers\Client\ToolsController;
use App\Models\{Vendor, Product, Client, AddonSet, Category, ProductVariant, CartProduct, UserWishlist, TaxCategory, VendorCategory, VendorSlot, VendorSlotDate, VendorDineinCategory, VendorDineinTable};

class CopyData implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The data to be processed by the job.
     *
     * @var array
     */
    protected $products;
    protected $ToolsController;
    protected $copy_to;
    protected $copy_from;
    protected $sku_url;
    protected $vendorCategory;
    protected $from_vendor;

    /**
     * Create a new job instance.
     *
     * @param  array  $products
     * @return void
     */
    public function __construct($products, $copy_to, $copy_from, $sku_url, $from_vendor, Vendor $vendor, Product $product, Client $client, AddonSet $addonSet, Category $category, VendorCategory $vendorCategory, VendorSlot $vendorSlot, VendorSlotDate $vendorSlotDate, VendorDineinCategory $vendorDineinCategory, VendorDineinTable $vendorDineinTable)
    {
        try{
        $this->products = $products;
        $this->ToolsController  = new ToolsController($vendor, $product, $client, $addonSet, $category, $vendorCategory, $vendorSlot, $vendorSlotDate, $vendorDineinCategory, $vendorDineinTable);
        $this->copy_to = $copy_to;
        $this->copy_from = $copy_from;
        $this->sku_url = $sku_url;
        $this->vendorCategory = $vendorCategory;
        $this->from_vendor = $from_vendor;
        foreach($this->products as $product){
            $product_slug = !is_null($product->title) ? $product->title : $product->url_slug;
            $product_sku = $this->sku_url . '.' . remove_special_chars($product_slug);
            $check_product = $product->getProductBySku($product_sku);
            if ($check_product) {
                $this->ToolsController->deleteProduct($check_product->id);
            }
            $this->ToolsController->addProduct($product, $this->copy_to, $this->copy_from, $product_sku);
        }

        /*unique Categories replicate */
        $v_c_categories = $this->vendorCategory->select('categories.id as category_id', 'vendor_categories.vendor_id as vendor_id', 'vendor_categories.status as status')
            ->join('categories', 'categories.id', '=', 'vendor_categories.category_id')
            ->where('categories.vendor_id', "!=", null)
            ->where('vendor_categories.vendor_id', $this->from_vendor->id)
            ->get();

        foreach ($v_c_categories as $row) {
            $category = Category::find($row->category_id);
            $new_category_result = $this->ToolsController->addCompleteCategory($category, $this->copy_to);
            if (VendorCategory::where(['category_id' => $new_category_result->id, 'vendor_id' => $new_category_result->vendor_id])->exists()) {
                $status = VendorCategory::where(['category_id' => $category->id, 'vendor_id' => $category->vendor_id])->first()->status;
                VendorCategory::where(['category_id' => $new_category_result->id, 'vendor_id' => $new_category_result->vendor_id])->update(['status' => $status]);
            } else {
                $status = VendorCategory::where(['category_id' => $category->id, 'vendor_id' => $category->vendor_id])->first()->status;
                VendorCategory::create(['category_id' => $new_category_result->id, 'vendor_id' => $new_category_result->vendor_id, 'status' => $status]);
            }
        }
        }catch(\Exception $e){
            \Log::info("error ".$e->getMessage());
        }
    }
}
