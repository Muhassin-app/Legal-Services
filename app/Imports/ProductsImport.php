<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Illuminate\Support\Collection;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\{Brand, Tag, ProductTag, Category, AddonSet, ProductAddon, ClientLanguage, CategoryTranslation, CsvProductImport, Product, ProductCategory, ProductTranslation, ProductVariant, ProductVariantSet, TaxCategory, Variant, VariantOption, VendorCategory, VendorMedia, ProductImage, Client, Vendor};
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use voku\helper\ASCII;

class ProductsImport implements ToCollection
{
	private $folderName = 'prods';
	public $vendor_id, $csv_product_import_id;

	public function  __construct($vendor_id, $csv_product_import_id)
	{
		$this->vendor_id = $vendor_id;
		$this->csv_product_import_id = $csv_product_import_id;

		$code = Client::orderBy('id', 'asc')->value('code');
		$this->folderName = '/' . $code . '/prods';
	}
	public function collection(Collection $rows)
	{
	    try{
			$i = 0;
			$data = array();
			$error = array();
			$variant_exist = 0;
			try{
			foreach ($rows as $row) {
			    $checker = 0;
			    if ($row[0] != "SKU") { //header of excel check
			        
			        if ($row[0] == "") { //if sku or handle is empty
			            $error[] = "Row " . $i . " : SKU  is empty";
			            $checker = 1;
			        }
			        if (Product::where('sku', $row[0])->exists()) { //if sku or handle is empty
			            $error[] = "Row " . $i . " : Product with this sku already exist";
			            $checker = 1;
			        }
			        // if ($row[3] == "") { //check if published is empty
			        //     $error[] = "Row " . $i . " : Please mark published either true or false";
			        //     $checker = 1;
			        // }
			        if ($row[4] == "") { // check if category is empty
			            $error[] = "Row " . $i . " : Category cannot be empty";
			            $checker = 1;
			        }
			        if ($row[4] != "") {
			            $category = $row[4];
			            $vendorCategoryExists = VendorCategory::with('category.translation')
			            ->whereHas('category.translation', function($q)use($category){
			                $q->select('category_translations.name')
			                ->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')
			                ->join('languages', 'category_translations.language_id', 'languages.id')
			                ->where('cl.is_active', 1)
			                ->where('category_translations.name', 'LIKE', $category);
			            })->where('vendor_id', $this->vendor_id)->first();
			            
			            if (!$vendorCategoryExists) { //check if category doesn't exist
			                $error[] = "Row " . $i . " : Category doesn't exist";
			                $checker = 1;
			            }
			            else{
			                if($vendorCategoryExists->status != 1){
			                    $error[] = "Row " . $i . " : This category is not activated for this vendor";
			                    $checker = 1;
			                }
			            }
			        }
			      /*  
			        if ($row[5] != "" && $row[6] != "") {
			            $variant_check = Variant::whereHas('category.translation_one' , function($query)use ($row){
			                $query->where('name' , $row[4]);
			            })->where(['title' => $row[5], 'variants.status' => 1])->first();
			            // $variant_check = Variant::where('title', $row[5])->first();
			            if (!$variant_check) {
			                $error[] = "Row " . $i . " : Option1 Name doesn't exist";
			                $checker = 1;
			            }
			            
			            $variant_option = VariantOption::where('title', $row[6])->first();
			            if (!$variant_option) {
			                $error[] = "Row " . $i . " : Option1 value doesn't exist";
			                $checker = 1;
			            }
			            
			            if ($variant_check && $variant_option) {
			                $checkVariantMatch = VariantOption::where(['title' => $row[6], 'variant_id' => $variant_check->id])->first();
			                if (!$checkVariantMatch) {
			                    $error[] = "Row " . $i . " : Option1 value is not available for this Name";
			                    $checker = 1;
			                } else {
			                    $variant_exist = 1;
			                }
			            }
			        }
			        if ($row[7] != "" && $row[8] != "") {
			            $variant_check = Variant::whereHas('category.translation_one' , function($query)use ($row){
			                $query->where('name' , $row[4]);
			            })->where(['title' => $row[7], 'variants.status' => 1])->first();
			            // $variant_check = Variant::where('title', $row[7])->first();
			            if (!$variant_check) {
			                $error[] = "Row " . $i . " : Option2 Name doesn't exist";
			                $checker = 1;
			            }
			            
			            $variant_option = VariantOption::where('title', $row[8])->first();
			            if (!$variant_option) {
			                $error[] = "Row " . $i . " : Option2 value doesn't exist";
			                $checker = 1;
			            }
			            
			            if ($variant_check && $variant_option) {
			                $checkVariantMatch = VariantOption::where(['title' => $row[8], 'variant_id' => $variant_check->id])->first();
			                if (!$checkVariantMatch) {
			                    $error[] = "Row " . $i . " : Option2 value is not available for this Name";
			                    $checker = 1;
			                } else {
			                    $variant_exist = 1;
			                }
			            }
			        }
			        
			        if ($row[9] != "" && $row[10] != "") {
			            $variant_check = Variant::whereHas('category.translation_one' , function($query)use ($row){
			                $query->where('name' , $row[4]);
			            })->where(['title' => $row[9], 'variants.status' => 1])->first();
			            // $variant_check = Variant::where('title', $row[9])->first();
			            if (!$variant_check) {
			                $error[] = "Row " . $i . " : Option3 Name doesn't exist";
			                $checker = 1;
			            }
			            
			            $variant_option = VariantOption::where('title', $row[10])->first();
			            if (!$variant_option) {
			                $error[] = "Row " . $i . " : Option3 value doesn't exist";
			                $checker = 1;
			            }
			            
			            if ($variant_check && $variant_option) {
			                $checkVariantMatch = VariantOption::where(['title' => $row[10], 'variant_id' => $variant_check->id])->first();
			                if (!$checkVariantMatch) {
			                    $error[] = "Row " . $i . " : Option3 value is not available for this Name";
			                    $checker = 1;
			                } else {
			                    $variant_exist = 1;
			                }
			            }
			        }*/
			        
			        if ($variant_exist == 1) {
			            if ($row[11] == "") {
			                $error[] = "Row " . $i . " : Variant Sku is empty";
			                $checker = 1;
			            } else {
			                $proVariant = ProductVariant::where('sku', $row[11])->first();
			                if ($proVariant) {
			                    $error[] = "Row " . $i . " : Variant Sku already exist";
			                    $checker = 1;
			                }
			            }
			        }
			        if ($row[11] != "") {
			            $proVariant = ProductVariant::where('sku', $row[11])->first();
			            if ($proVariant) {
			                $error[] = "Row " . $i . " : Variant Sku already exist";
			                $checker = 1;
			            }
			        }
			        
			        if($row[19] != ""){
			            $tax_category = TaxCategory::where('title', "LIKE", $row[19])->first();
			            if(!$tax_category){
			                $error[] = "Row " . $i . " : Tax Category doesn't exist";
			                $checker = 1;
			            }
			        }
			        if(isset($row[23]) && $row[23] != ""){
			            
			            foreach (explode(',', $row[23]) as $titleKey => $Addontitle) {
			                $vendorAddonSetExists =AddonSet::where('title', "LIKE", $Addontitle)->where('vendor_id', $this->vendor_id)->first();
			                if(!$vendorAddonSetExists){
			                    $error[] = "Row " . $i . " : Addon doesn't exist";
			                    $checker = 1;
			                    break;
			                }
			            }
			        }
			        if(isset($row[24]) &&$row[24] != ""){
			            foreach (explode(',', $row[24]) as $titleKey => $tagtitle) {
			                $vendorTagtitle = Tag::with('primary')->whereHas('primary', function($q)use($tagtitle){
			                    $q->select('tag_translations.name')
			                    ->join('client_languages as cl', 'cl.language_id', 'tag_translations.language_id')
			                    ->join('languages', 'tag_translations.language_id', 'languages.id')
			                    ->where('cl.is_active', 1)
			                    ->where('tag_translations.name', 'LIKE', $tagtitle);
			                })->first();
			                
			                
			                if(!$vendorTagtitle){
			                    $error[] = "Row " . $i . " : Tags doesn't exist";
			                    $checker = 1;
			                    break;
			                }
			            }
			        }
			        if ($checker == 0) {
			            $data[] = $row;
			        }
			     }
			$i++;
	       }
				if (!empty($data)) {
				    foreach ($data as $da) {
				        // array_map("utf8_encode", $da);
				        $da[2] = str_replace("","’",$da[2]);				        
				        if (!Product::where('sku', $da[0])->exists()) {
				            $brand_id = null;
				            $tax_category_id = null;
				            
				            if($da[18] != ""){
				                $brand = Brand::where('title', "LIKE", $da[18])->first();
				                if($brand){
				                    $brand_id = $brand->id;
				                }
				            }
				            
				            if($da[19] != ""){
				                
				                $tax_category = TaxCategory::where('title', "LIKE", $da[19])->first();
				                if($tax_category){
				                    $tax_category_id = $tax_category->id;
				                }
				            }
				            
				            $category = $da[4];
				            
				            $category = VendorCategory::with('category.translation')
				            ->whereHas('category.translation', function($q)use($category){
				                $q->select('category_translations.name')
				                ->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')
				                ->join('languages', 'category_translations.language_id', 'languages.id')
				                ->where('cl.is_active', 1)
				                ->where('category_translations.name', 'LIKE', $category);
				            })->where('vendor_id', $this->vendor_id)->first();
				            
				            
				            $product = Product::insertGetId([
				                'type_id' => 1,
				                'sku' => $da[0],
				                'is_featured' => 0,
				                'is_physical' => 0,
				                'has_inventory' => 0,
				                'url_slug' => $da[0],
				                'brand_id' => $brand_id,
				                'requires_shipping' => 0,
				                'Requires_last_mile' => 0,
				                'sell_when_out_of_stock' => 0,
				                'vendor_id' => $this->vendor_id,
				                'category_id' =>$category->category_id,
				                'tax_category_id' => $tax_category_id,
				                'title' => ($da[1] == "") ? "" : $da[1],
				                'is_live' => ($da[3] == 'TRUE') ? 1 : 0,
				                'body_html' => ($da[2] == "") ? "" : $da[2],
				            ]);
				            if(isset($da[23]) && $da[23] != ""){
    				            foreach (explode(',', $da[23]) as $titleKey => $Addontitle) {
    				                $vendorAddonSetExists =AddonSet::where('title', "LIKE", $Addontitle)->where('vendor_id', $this->vendor_id)->first();
    				                if($vendorAddonSetExists){
    				                    $addonsArray= [
    				                        'product_id' => $product,
    				                        'addon_id' => $vendorAddonSetExists->id
    				                    ];
    				                    ProductAddon::insert($addonsArray);
    				                }
    				            }
				            }
				            
				            
				            //insertion into product category
				            $cat = [
				                'product_id' => $product,
				                'Category_id' => $category->category_id,
				            ];
				            
				            ProductCategory::insert($cat);
				            
				            if(isset($da[24]) && $da[24] != ""){
				                // $delete = ProductTag::where('product_id', $product)->delete();
				                foreach (explode(',', $da[24]) as $titleKey => $tagtitle) {
				                    $vendorTagtitle = Tag::with('primary')->whereHas('primary', function($q)use($tagtitle){
				                        $q->select('tag_translations.name')
				                        ->join('client_languages as cl', 'cl.language_id', 'tag_translations.language_id')
				                        ->join('languages', 'tag_translations.language_id', 'languages.id')
				                        ->where('cl.is_active', 1)
				                        ->where('tag_translations.name', 'LIKE', $tagtitle);
				                    })->first();
				                    
				                    
				                    if($vendorTagtitle){
				                        $tagSetArray= [
				                            'product_id' => $product,
				                            'tag_id' => $vendorTagtitle->id
				                        ];
				                        ProductTag::insert($tagSetArray);
				                    }
				                }
				            }
				            
				            $client_lang = ClientLanguage::where('is_primary', 1)->first();
				            if (!$client_lang) {
				                $client_lang = ClientLanguage::where('is_active', 1)->first();
				            }
				            
				            //insertion into product translations
				            $datatrans[] = [
				                'title' => ($da[1] == "") ? "" : $da[1],
				                'body_html' => ($da[2] == "") ? "" : $da[2],
				                'meta_title' => '',
				                'meta_keyword' => '',
				                'meta_description' => '',
				                'product_id' => $product,
				                'language_id' => $client_lang->language_id
				            ];
				            
				            ProductTranslation::insert($datatrans);
				            
				            if ($da[5] != "" || $da[7] != "" || $da[9] != "") {
				                $product_hasvariant = Product::where('id', $product)->first();
				                $product_hasvariant->has_variant = 1;
				                $product_hasvariant->save();
				                //inserting product variant
				                if($da[7] != ""){
    				                $proVariant = ProductVariant::updateOrcreate(['sku' => $da[7], 'title' => $da[7], 'product_id' => $product], [
    				                    'sku' => $da[7],
    				                    'title' => $da[7],
    				                    'product_id' => $product,
    				                    'quantity' => (!empty($da[13]))?$da[13]:0,
    				                    'price' => $da[12],
    				                    'compare_at_price' => $da[14],
    				                    'cost_price' => $da[17],
    				                    'barcode' => $this->generateBarcodeNumber(),
    				                ]);
				                }
				                if ($da[5] != "") {
				                    $variant = Variant::whereHas('category.translation_one' , function($query)use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[5], 'variants.status' => 1])->first();
				                    // $variant = Variant::where('title', $da[5])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title' => $da[6], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				                
				                if ($da[7] != "") {
				                    // $variant = Variant::where('title', $da[7])->first();
				                    $variant = Variant::whereHas('category.translation_one' , function($query)use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[7], 'variants.status' => 1])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title' => $da[8], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				                
				                if ($da[9] != "") {
				                    // $variant = Variant::where('title', $da[9])->first();
				                    $variant = Variant::whereHas('category.translation_one' , function($query)use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[9], 'variants.status' => 1])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title' => $da[10], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				            }
				            else{
				                $proVariant = new ProductVariant();
				                $proVariant->sku = $da[0];
				                $proVariant->product_id = $product;
				                $proVariant->barcode = $this->generateBarcodeNumber();
				                $proVariant->quantity = $da[20]??0;
				                $proVariant->price = $da[21]??"";
				                $proVariant->compare_at_price = $da[22]??"";
				                $proVariant->save();
				            }
				            //images
				            if (!empty($da[16])) {
				                foreach (explode(',', $da[16]) as $file_key => $file) {
				                    $img = new VendorMedia();
				                    $img->media_type = 1;
				                    $img->vendor_id = $this->vendor_id;
				                    $img->path = $file;
				                    $img->save();
				                    $image = new ProductImage();
				                    $image->product_id = $product;
				                    $image->is_default = ($file_key == 0)?1:0;
				                    $image->media_id = $img->id;
				                    $image->save();
				                }
				            }
				        }
				        else{
				            $product_id = Product::where('sku', $da[0])->first();
				            
				            //update product
				            
				            $brand_id = null;
				            $tax_category_id = null;
				            
				            if($da[18] != ""){
				                $brand = Brand::where('title', "LIKE", $da[18])->first();
				                if($brand){
				                    $brand_id = $brand->id;
				                }
				            }
				            
				            
				            if($da[19] != ""){
				                
				                $tax_category = TaxCategory::where('title', "LIKE", $da[19])->first();
				                if($tax_category){
				                    $tax_category_id = $tax_category->id;
				                }
				            }
				            
				            $category = $da[4];
				            
				            $category = VendorCategory::with('category.translation')
				            ->whereHas('category.translation', function($q)use($category){
				                $q->select('category_translations.name')
				                ->join('client_languages as cl', 'cl.language_id', 'category_translations.language_id')
				                ->join('languages', 'category_translations.language_id', 'languages.id')
				                ->where('cl.is_active', 1)
				                ->where('category_translations.name', 'LIKE', $category);
				            })->where('vendor_id', $this->vendor_id)->first();
				            
				            if(empty($category)){
				                
				            }
				            Product::where('id',$product_id->id)->update([
				                'type_id' => 1,
				                'sku' => $da[0],
				                'is_featured' => 0,
				                'is_physical' => 0,
				                'has_inventory' => 0,
				                'url_slug' => $da[0],
				                'brand_id' => $brand_id,
				                'requires_shipping' => 0,
				                'Requires_last_mile' => 0,
				                'sell_when_out_of_stock' => 0,
				                'vendor_id' => $this->vendor_id,
				                'category_id' =>$category->category_id,
				                'tax_category_id' => $tax_category_id,
				                'title' => ($da[1] == "") ? "" : $da[1],
				                'is_live' => ($da[3] == 'TRUE') ? 1 : 0,
				                'body_html' => ($da[2] == "") ? "" : $da[2],
				            ]);
				            
				            
				            $delete = ProductAddon::where('product_id', $product_id->id)->delete();
				            $delete = ProductTag::where('product_id', $product_id->id)->delete();
				            if(isset($da[23]) && $da[23] != ""){
    				            foreach (explode(',', $da[23]) as $titleKey => $Addontitle) {
    				                $vendorAddonSetExists =AddonSet::where('title', "LIKE", $Addontitle)->where('vendor_id', $this->vendor_id)->first();
    				                if($vendorAddonSetExists){
    				                    $addonsArray= [
    				                        'product_id' => $product_id->id,
    				                        'addon_id' => $vendorAddonSetExists->id
    				                    ];
    				                    ProductAddon::insert($addonsArray);
    				                }
    				            }
    				        }
    				        if(isset($da[24]) &&$da[24] != ""){
				                foreach (explode(',', $da[24]) as $titleKey => $tagtitle) {
				                    $vendorTagtitle = Tag::with('primary')->whereHas('primary', function($q)use($tagtitle){
				                        $q->select('tag_translations.name')
				                        ->join('client_languages as cl', 'cl.language_id', 'tag_translations.language_id')
				                        ->join('languages', 'tag_translations.language_id', 'languages.id')
				                        ->where('cl.is_active', 1)
				                        ->where('tag_translations.name', 'LIKE', $tagtitle);
				                    })->first();
				                    
				                    
				                    if($vendorTagtitle){
				                        $tagSetArray= [
				                            'product_id' => $product_id->id,
				                            'tag_id' => $vendorTagtitle->id
				                        ];
				                        ProductTag::insert($tagSetArray);
				                    }
				                }
				            }
				            if ($da[5] != "" || $da[7] != "" || $da[9] != "") {
				                $product_hasvariant = Product::where('id', $product_id->id)->first();
				                $product_hasvariant->has_variant = 1;
				                $product_hasvariant->save();
				                //inserting product variant
				                if($da[7] != ""){
    				                $proVariant = ProductVariant::updateOrcreate(['sku' => $da[7], 'title' => $da[7], 'product_id' => $product_id->id],[
    				                    'sku' => $da[7],
    				                    'title' => $da[7],
    				                    'product_id' => $product_id->id,
    				                    'quantity' => (!empty($da[13]))?$da[13]:0,
    				                    'price' => $da[12],
    				                    'compare_at_price' => $da[14],
    				                    'cost_price' => $da[17],
    				                    'barcode' => $this->generateBarcodeNumber(),
    				                ]);
				                }
				                if ($da[5] != "") {
				                    $variant = Variant::whereHas('category.translation_one' , function($query) use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[5], 'variants.status' => 1])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title'=> $da[6], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product_id->id;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				                
				                if ($da[7] != "") {
				                    $variant = Variant::whereHas('category.translation_one' , function($query)use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[7], 'variants.status' => 1])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title' => $da[8], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product_id->id;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				                
				                if ($da[9] != "") {
				                    $variant = Variant::whereHas('category.translation_one' , function($query)use ($da){
				                        $query->where('name' , $da[4]);
				                    })->where(['title' => $da[9], 'variants.status' => 1])->first();
				                    if ($variant) {
				                    $variant_optionn = VariantOption::where(['title' => $da[10], 'variant_id' => $variant->id])->first();
				                    //inserting product variant sets
				                    $proVariantSet = new ProductVariantSet();
				                    $proVariantSet->product_id = $product_id->id;
				                    $proVariantSet->product_variant_id = $proVariant;
				                    $proVariantSet->variant_type_id = $variant->id;
				                    $proVariantSet->variant_option_id = $variant_optionn->id;
				                    $proVariantSet->save();
				                    }
				                }
				            }else{
				                $proVariant = ProductVariant::where('sku',$da[0])->first();
				                if( $proVariant){
				                    $proVariant->sku = $da[0];
				                    $proVariant->price = $da[21]??"";
				                    $proVariant->compare_at_price = $da[22]??"";
				                    $proVariant->save();
				                }
				            }
				            
				        }
				    }
				}
        	} catch(\Exception $ex){
        	    $error[] = "Other: " .$ex->getMessage();
				\Log::info($ex->getMessage()."".$ex->getLine());
        	}
			$vendor_csv = CsvProductImport::where('vendor_id', $this->vendor_id)->where('id', $this->csv_product_import_id)->first();
			if (!empty($error)) {
				$vendor_csv->status = 3;
				$vendor_csv->error = json_encode($error);
			} else {
				$vendor_csv->status = 2;
			}
			$vendor_csv->save();
        } catch(\Exception $ex){
            $error[] = "Other: " .$ex->getMessage();
			\Log::info($ex->getMessage()."".$ex->getLine());
        }
	}
	private function generateBarcodeNumber()
	{
		$random_string = substr(md5(microtime()), 0, 14);
		while (ProductVariant::where('barcode', $random_string)->exists()) {
			$random_string = substr(md5(microtime()), 0, 14);
		}
		return $random_string;
	}
}