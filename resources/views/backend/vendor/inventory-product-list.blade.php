
    <div class="col-12">
        <div class="table-responsive">
            <form name="inventry-import" method="post" action="{{route('post.inventory.store.products')}}">
                @csrf()
                <input name="vendor_id" value="{{$vendor_id}}" type="hidden">
                <input name="vendor_slug" value="{{$vendor_slug}}" type="hidden">
                <table class="table table-centered table-nowrap table-striped" id="">
                    <thead>
                        <tr>
                            <th><input type="checkbox" class="all-product_check"
                                    name="all_product_id" id="all-product_check"></th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Category') }}</th>
                        

                        </tr>
                    </thead>
                    <tbody id="post_list">
                        @foreach ($store_product as $key => $product)
                            <tr data-row-id="{{ $product['id'] }}">  
                                <td><input type="checkbox" class="single_product_check"
                                        name="productids[]" 
                                        value="{{ $product['id'] }}"></td>
                                <td> {{ Str::limit(isset($product['primary']['title']) && !empty($product['primary']['title']) ? $product['primary']['title'] : '', 30) }}</td>
                                <td> {{ $product['category'] ? $product['category']['cat']['name'] : 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="col-12 p-0" id="inventory_category_list">


                </div>

                


               
            </form>
        </div>
    </div>
    <script>
        $(".all-product_check").click(function() {
            if ($(this).is(':checked')) {
                $("#action_product_button").css("display", "block");
                $('.single_product_check').prop('checked', true);
            } else {
                $("#action_product_button").css("display", "none");
                $('.single_product_check').prop('checked', false);
            }
        });

        
        $(document).on('click',".single_product_check, .all-product_check",function() {
            var productids = [];
            $("input[name='productids[]']:checked").each(function(index, elem){
                productids.push($(elem).val());
            });

           
            var url = "{{ route('get.inventory.category.products')}}";
            $.ajax({
                url: url,
                type: "POST",
                dataType: "json",
                data: {productids: productids},
                success: function(response) {
                    if (response.success == true) {
                        
                        $('#inventory_category_list').html('');
                        $('#inventory_category_list').html(response.html);
                        
                    }
                }
            }); 
             
        });
        </script>