@if(count($inventory_category))
    @foreach($inventory_category as $key => $category)
        <div class="col-md-12 mb-3">
            <div class="row">
                   <div class="col-6">
                    @if($key == 0)
                    <label for="">{{__('Inventory Category')}}</label>
                    @endif

                    <label for=""  class="form-control">
                        {{ $category['translation_one']['name']??'' }}</label>
                    
                </div>
                <div class="col-6">
                    @if($key == 0)
                    <label for="">{{__('Order Category')}}</label>
                    @endif
                    <select name="order_category[]" class="form-control" >
                        @foreach ($order_category as $ocat)
                          <option value="{{ $category['id']}}_{{$ocat->id}}">{{ $ocat['translation_one']['name']??'' }}</option>
                        @endforeach
                    </select>
                </div>
            
            </div>
        </div> 
            
    @endforeach

<div class="col-12">
    <button class="btn btn-info waves-effect waves-light w-100">{{ __("Import") }}</button>
</div>

@endif
                   