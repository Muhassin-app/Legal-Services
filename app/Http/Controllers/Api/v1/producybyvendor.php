$vendor_categories = VendorCategory::where('vendor_id', $vid)->with(['category.translation' => function($q) use($langId){
                    $q->where('category_translations.language_id', $langId);
                }])->whereHas('category.products')->with(['category.products' => function ($q)use($langId,$userid){
                        $q->where('is_live', 1)->with([
                            'category.categoryDetail', 'category.categoryDetail.translation' => function($q) use($langId){
                            $q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
                            ->where('category_translations.language_id', $langId);
                        }, 
                        'inwishlist' => function($qry) use($userid){
                            $qry->where('user_id', $userid);
                        },
                        'media.image',
                        'addOn' => function ($q1) use ($langId) {
                            $q1->join('addon_sets as set', 'set.id', 'product_addons.addon_id');
                            $q1->join('addon_set_translations as ast', 'ast.addon_id', 'set.id');
                            $q1->select('product_addons.product_id', 'set.min_select', 'set.max_select', 'ast.title', 'product_addons.addon_id');
                            $q1->where('set.status', 1)->where('ast.language_id', $langId);
                        },
                        'addOn.setoptions' => function ($q2) use ($langId) {
                            $q2->join('addon_option_translations as apt', 'apt.addon_opt_id', 'addon_options.id');
                            $q2->select('addon_options.id', 'addon_options.title', 'addon_options.price', 'apt.title', 'addon_options.addon_id');
                            $q2->where('apt.language_id', $langId);
                        },
                        'translation' => function($q) use($langId){
                            $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description','language_id')->where('language_id', $langId)->take(1);
                        },
                        'variant' => function($q) use($langId){
                            $q->select('id','sku', 'product_id', 'quantity', 'price', 'barcode', 'compare_at_price')->orderBy('quantity', 'desc');
                            // $q->groupBy('product_id');
                        },'variant.checkIfInCartApp', 'checkIfInCartApp',
                         'tags.tag.translations' => function ($q) use ($langId) {
                            $q->where('language_id', $langId);
                        }
                    ])->with(['variantSet' => function ($z) use ($langId) {
                                        $z->join('variants as vr', 'product_variant_sets.variant_type_id', 'vr.id');
                                        $z->join('variant_translations as vt', 'vt.variant_id', 'vr.id');
                                        $z->select('product_variant_sets.product_id', 'product_variant_sets.product_variant_id', 'product_variant_sets.variant_type_id', 'vr.type', 'vt.title');
                                        $z->where('vt.language_id', $langId);
                                        $z->orderBy('product_variant_sets.variant_type_id', 'asc');
                                    },'variantSet.option2'=> function ($zx) use ($langId) {
                                        $zx->where('vt.language_id', $langId);
                                    }]);
                    }])->where('status', 1)->get();