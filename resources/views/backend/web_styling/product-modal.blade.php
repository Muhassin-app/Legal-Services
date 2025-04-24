
<select class="form-control select2search" id='product_id' name="product_id" data-placeholder="Choose ..." required>
    <option value="">{{ __("Select Product") }}</option>
    @foreach($products as $product)
    <option value="{{$product->id}}" >
        {{$product->translation[0]->title}}
    </option>
    @endforeach
</select>
<script>
$(".select2search").select2();
</script>

