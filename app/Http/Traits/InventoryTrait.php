<?php

namespace App\Http\Traits;

use DB;
use Auth;
use Carbon\Carbon;

trait InventoryTrait
{

    public function saveBrand($i_product, $request)
    {
        $order_brand_id = null;
        $brand_details = $i_product['brand'];
        if (@$brand_details) {
            \DB::table('brands')->updateOrInsert([
                'title' => $brand_details['title']
            ], [
                'image' => $brand_details['image'],
                'image_banner' => $brand_details['image_banner'],
                'status' => $brand_details['status']
            ]);
            $order_brand =  \DB::table('brands')->where('title', $brand_details['title'])->first();
            $order_brand_id = $order_brand->id;

            \DB::table('brand_categories')->updateOrInsert([
                'brand_id' => $order_brand->id,
                'category_id' => $request['order_cat']
            ], [
                'brand_id' => $order_brand->id,
                'category_id' => $request['order_cat']
            ]);
            \DB::table('brand_translations')->updateOrInsert([
                'title' => $brand_details['title'],
                'brand_id' => $order_brand->id
            ], [
                'title' => $brand_details['title'],
                'brand_id' => $order_brand->id
            ]);
        }
        return $order_brand_id;
    }
    public function saveTax($i_product, $request)
    {
        $order_product_tax_categories_id = null;
        $inventory_product_tax_category = @$i_product['tax']['tax_categories'];
        if (@$inventory_product_tax_category) {
            \DB::table('tax_categories')->updateOrInsert([
                'title' => $inventory_product_tax_category['title'],
                'code' => $inventory_product_tax_category['code']
            ], [
                'title' => $inventory_product_tax_category['title'],
                'code' => $inventory_product_tax_category['code'],
                'description' => $inventory_product_tax_category['description'],
                'is_core' => $inventory_product_tax_category['is_core']
                // 'description' => $inventory_product_tax_category->code,
            ]);

            $order_product_tax_categories =  \DB::table('tax_categories')->where([
                'title' => $inventory_product_tax_category['title'],
                'code' => $inventory_product_tax_category['code']
            ])->first();
            $order_product_tax_categories_id = $order_product_tax_categories->id;

            $inventory_tax_cat = $i_product['tax']['tax_rate_categories'];
            $tax_rates = $i_product['tax']['tax_rates'];

            if (!empty($tax_rates)) {
                foreach ($tax_rates as $tax_key => $inventory_tax_rate) {


                    \DB::table('tax_rates')->updateOrInsert([
                        'identifier' => $inventory_tax_rate['identifier'],
                    ], [
                        'identifier'    => $inventory_tax_rate['identifier'],
                        'is_zip'        => $inventory_tax_rate['is_zip'],
                        'zip_code'      => $inventory_tax_rate['zip_code'],
                        'zip_from'      => $inventory_tax_rate['zip_from'],
                        'zip_to'        => $inventory_tax_rate['zip_to'],
                        'state'         => $inventory_tax_rate['state'],
                        'country'       => $inventory_tax_rate['country'],
                        'tax_rate'      => $inventory_tax_rate['tax_rate'],
                        'tax_amount'    => $inventory_tax_rate['tax_amount'],
                    ]);

                    $order_tax_rate = \DB::table('tax_rates')->where('identifier', $inventory_tax_rate['identifier'])->first();
                    \DB::table('tax_rate_categories')->updateOrInsert([
                        'tax_cate_id' => $order_product_tax_categories_id,
                        'tax_rate_id' => $order_tax_rate->id,
                    ], [
                        'tax_cate_id' => $order_product_tax_categories_id,
                        'tax_rate_id' => $order_tax_rate->id,
                    ]);
                }
            }
        }
        return $order_product_tax_categories_id;
    }


    public function saveProduct($i_product)
    {
        $order_product_id = '';
        $product_update_create = $i_product;

        unset($product_update_create['brand']);
        unset($product_update_create['tax']);
        unset($product_update_create['product_varaint_set']);
        unset($product_update_create['media']);
        unset($product_update_create['variantData']);
        unset($product_update_create['addon_sets']);
        unset($product_update_create['product_translations']);
        unset($product_update_create['variants']);
        unset($product_update_create['i_id']);
        unset($product_update_create['attribute_keys']);
        unset($product_update_create['attribute_value']);
        unset($product_update_create['home_service']);
        unset($product_update_create['store_visit']);

        if ($i_product['sku'] != null) {
            $product_exists = \DB::table('products')->where('sku', $i_product['sku'])->first();
            if (empty($product_exists)) {
                $order_product_id = \DB::table('products')->insertGetId($product_update_create);
            } else {
                \DB::table('products')->where('sku', $i_product['sku'])->update($product_update_create);
                $order_product_id = $product_exists->id;
            }
        } else {
            $order_product_id = \DB::table('products')->insertGetId($product_update_create);
        }

        return $order_product_id;
    }


    public function saveProductTranslation($product_translations, $order_product_id)
    {
        foreach ($product_translations as  $product_translation) {     # import product translation
            $product_trans = [
                'title'         => $product_translation['title'],
                'body_html'     => $product_translation['body_html'],
                'meta_title'    => $product_translation['meta_title'],
                'meta_keyword'  => $product_translation['meta_keyword'],
                'meta_description' => $product_translation['meta_description'],
                'product_id'    => $order_product_id,
                'language_id'   => @$product_translation['language_id'] ?? 1,
            ];
            $product_translation_import = \DB::table('product_translations')->updateOrInsert(['product_id' => $order_product_id], $product_trans);
        }

        return true;
    }


    public function saveVariant($productVariants, $order_product_id, $request)
    {

        \DB::table('product_variant_sets')->where('product_id', $order_product_id)->delete();

        $order_side_variant_id = '';
        if (!empty($productVariants)) {
            foreach ($productVariants as $key => $val) {

                $varaint = \DB::table('variants')->where(['title' => $val['title']])->first();
                // dump($varaint);
                if (!empty($varaint)) {
                    $order_side_variant_id = $varaint->id;
                } else {

                    $order_side_variant = \DB::table('variants')->insertGetId([
                        'title' => $val['title'],
                        'type' => $val['type'],
                        'position' => $val['position'],
                        'status' => $val['status'],
                        'created_at' =>  Carbon::now(),
                        'updated_at' =>  Carbon::now(),
                    ]);
                    $order_side_variant_id = $order_side_variant;
                }


                # Variant Translation

                $vaiant_translation = $val['trans'];

                if (!empty($vaiant_translation)) {
                    $order_variant_translations = \DB::table('variant_translations')->where(['title' => $vaiant_translation['title']])->first();

                    if (!empty($order_variant_translations)) {
                        $order_variant_translations_id = $order_variant_translations->id;
                    } else {
                        \DB::table('variant_translations')->insertGetId([
                            'title' => $vaiant_translation['title'],
                            'variant_id' => $order_side_variant_id,
                            'language_id' => @$vaiant_translation['language_id'] ?? 1,
                            'created_at' => Carbon::now(),
                            'updated_at' => Carbon::now(),
                        ]);
                    }
                }



                # Variant Option
                if (!empty($val['option']) && !empty($order_side_variant_id)) {
                    foreach ($val['option'] as $key => $value) {
                        # variant option 

                        $variant_options = \DB::table('variant_options')->where(['title' => $value['title']])->first();
                        if (!empty($variant_options)) {

                            $order_varaint_option_id = $variant_options->id;
                        } else {

                            $order_varaint_option_id = \DB::table('variant_options')->insertGetId([
                                'title' => $value['title'],
                                'variant_id' => $order_side_variant_id,
                                'hexacode' => $value['hexacode'],
                                'position' => $value['position'],
                            ]);
                        }

                        # variant option translation
                        if (!empty($value['trans'])) {

                            # variant option translations table
                            if (!empty($value['trans']['title'])) {
                                $variant_option_translations = \DB::table('variant_option_translations')->where(['title' => $value['trans']['title'], 'variant_option_id' => $order_varaint_option_id])->first();

                                if (!empty($variant_option_translations)) {
                                    $variant_option_translations_id = $variant_option_translations->id;
                                } else {
                                    \DB::table('variant_option_translations')->insertGetId([
                                        'title' => $value['trans']['title'],
                                        'variant_option_id' => $order_varaint_option_id,
                                        'language_id' => optional($value['trans'])['language_id'] ?? null,
                                    ]);
                                }
                            }
                        }
                    }
                }


                # variant category
                if (!empty($val['varcategory'])) {
                    // dd('inside the if part');
                    foreach ($val['varcategory'] as $key1 => $val1) {
                        $variant_categories = \DB::table('variant_categories')->where([
                            'variant_id' => $order_side_variant_id,
                            'category_id' => $request['order_cat']
                        ])->first();

                        if (empty($variant_categories)) {
                            \DB::table('variant_categories')->insertGetId([
                                'variant_id' => $order_side_variant_id,
                                'category_id' => $request['order_cat']
                            ]);
                        }
                    }
                }
            }
        }

        return true;
    }

    public function saveVariantSet($product_varaint_set, $i_product, $request)
    {
        if (!empty($product_varaint_set)) {
            foreach ($product_varaint_set as $key => $val) {
                $product_id         = $val['product_id'];
                $product_variant_id = $val['product_variant_id'];
                $variant_type_id    = $val['variant_type_id'];
                $variant_option_id = $val['variant_option_id'];

                # first find product sku
                $products = \DB::table('products')->where(['sku' => $i_product['sku']])->first();
                if (!empty($products)) {
                    $order_side_product_id = $products->id;

                    # then find out the variant of product
                    $product_variant = $val['product_variant'];
                    $order_side_product_varaint = '';

                    if (!empty($product_variant)) {
                        $order_side_product_varaint = \DB::table('product_variants')->where(['sku' => $product_variant['sku']])->first();
                    }

                    # then find out the variant type id 
                    $variant = $val['variant_detail'];

                    if (!empty($variant)) {
                        $order_side_varaint = \DB::table('variants')->where(['title' => $variant['title']])->first();

                        # then find out the variant option id
                        $variant_option = $val['option_data'];
                        $order_side_variant_option =  \DB::table('variant_options')->where(['title' => $variant_option['title']])->first();
                        $order_side_variant_option_id = $order_side_variant_option->id;

                        \DB::table('product_variant_sets')->insert([
                            'product_id' => $order_side_product_id,
                            'product_variant_id' => $order_side_product_varaint->id ?? null,
                            'variant_type_id' => $order_side_varaint->id ?? null,
                            'variant_option_id' => $order_side_variant_option_id ?? null,
                        ]);
                    }
                }
            }
        }
        return true;
    }

    public function productApiAddons($product_addons, $order_product_id)
    {
        if (!empty($product_addons)) {
            foreach ($product_addons as $add_key => $addon_sets) {

                $order_addons = [
                    "title"         => $addon_sets['title'],
                    "min_select"    => $addon_sets['min_select'],
                    "max_select"    => $addon_sets['max_select'],
                    "position"      => $addon_sets['position'],
                    "status"        => $addon_sets['status'],
                    "is_core"       => $addon_sets['is_core'],
                    "vendor_id"     => $addon_sets['vendor_id']
                ];
                $order_addon_sets = \DB::table('addon_sets')->updateOrInsert(['vendor_id' => $addon_sets['vendor_id'], 'title' => $addon_sets['title']], $order_addons);
                $addon_sets = \DB::table('addon_sets')->where(['vendor_id' => $addon_sets['vendor_id'], 'title' => $addon_sets['title']])->first();
                $product_addon_set = \DB::table('product_addons')->updateOrInsert([
                    'product_id' => $order_product_id,
                    'addon_id' => $addon_sets->id,
                ], [
                    'product_id' => $order_product_id,
                    'addon_id' => $addon_sets->id,
                ]);
            }
        }
        return true;
    }


    public function saveProductMedia($medias, $order_product_id, $order_vendor_id)
    {
        $mediaIds =  \DB::table('product_images')->where('product_id', $order_product_id)->pluck('media_id');
        if (!empty($mediaIds)) {
            \DB::table('vendor_media')->whereIn('id', $mediaIds)->delete();
            \DB::table('product_images')->where('product_id', $order_product_id)->delete();
        }
        if (!empty($medias)) {
            foreach ($medias as $media) {
                $product_media = [
                    "media_type" => $media['image']['media_type'],
                    "vendor_id" => $order_vendor_id,
                    "path" => $media['image']['path']['db_image']
                ];
                $vendor_media_import =  \DB::table('vendor_media')->insertGetId($product_media);
                $product_image  = [
                    "product_id" => $order_product_id,
                    "media_id" => $vendor_media_import,
                    "is_default" => $media['is_default']
                ];
                \DB::table('product_images')->insertGetId($product_image);
            }
        }
        return true;
    }

    public function saveProductVariant($variants, $order_product_id, $order_vendor_id)
    {

        // # Add product variant
        foreach ($variants as $variant) {     # import product variant
            $product_variant = [
                "sku"           => $variant['sku'],
                "title"         => $variant['title'],
                "quantity"      => $variant['quantity'],
                "price"         => $variant['price'],
                "position"      => $variant['position'],
                "compare_at_price" => $variant['compare_at_price'],
                "barcode"       => $variant['barcode'],
                "expiry_date"       => $variant['expiry_date'],
                "cost_price"    => $variant['cost_price'],
                "currency_id"   => $variant['currency_id'],
                "tax_category_id" => $variant['tax_category_id'],
                "inventory_policy" => $variant['inventory_policy'],
                "fulfillment_service" => $variant['fulfillment_service'],
                "inventory_management" => $variant['inventory_management'],
                "status"        => $variant['status'],
                "container_charges" => $variant['container_charges'],
                "product_id"    => $order_product_id,
            ];
            $random_string = substr(md5(microtime()), 0, 14);
            while (\DB::table('product_variants')->where('barcode', $random_string)->exists()) {
                $random_string = substr(md5(microtime()), 0, 14);
            }
            $product_variant['barcode'] = $random_string;
            $product_variant_import =  \DB::table('product_variants')->updateOrInsert(['sku' => $variant['sku']], $product_variant);
        }
        return true;
    }
    public function saveProductCategory($order_cat, $order_product_id)
    {
        $data = [ 'category_id' => $order_cat ];
        \DB::table('product_categories')->updateOrInsert(['product_id' => $order_product_id], $data);
        return true;
    }
}
