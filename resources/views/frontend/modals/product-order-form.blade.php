<form id="product-order-form-name" class="product-order-form-name_{{ $product_faqs->first() ? $product_faqs->first()->product_id : '' }}" name="product-order-form-name" class="theme-form" action="javascript:void(0)" method="post" enctype="multipart/form-data">
    <div class="form-row"> 
            @foreach($product_faqs as $key => $qs)
                @if(!$qs->translations->isEmpty())
                    <div class="col-md-12 mb-3">
                        <label for="review"><b>{{$qs->translations->first()->name}}</b></label>
                        {{-- <input type="hidden" name="product_order_form[{{$key}}]['question']" value="{{$qs->translations->first()->name}}"> (($qs->is_required)?"required":"") --}}
                        @if(!$qs->selection->isEmpty())
                        <select name="{{$qs->translations->first()->name}}" class="form-control mb-0" data-required="{{$qs->is_required}}"  data-product_faq_id="{{$qs->translations->first()->product_faq_id}}">
                            <option value="">Select option</option>
                        @foreach($qs->selection as $qs1)
                            <option value="{{$qs1->translations->first()->name}}">{{$qs1->translations->first()->name}}</option>
                        @endforeach  
                        </select>
                        @elseif($qs->file_type == 'file')
                        <input type="file" class="form-control mb-0"  name="{{$qs->translations->first()->name}}" data-product_faq_id="{{$qs->translations->first()->product_faq_id}}" data-required={{$qs->is_required}} >
                        @else
                        <input type="text" class="form-control mb-0"  name="{{$qs->translations->first()->name}}" data-product_faq_id="{{$qs->translations->first()->product_faq_id}}" data-required={{$qs->is_required}} >
                        @endif
                    </div>
                @endif
            @endforeach                   
        <span class="text-danger product_order_form_error mb-2" id="error-msg"></span>
        <span class="text-success" id="success-msg"></span>
        <div class="col-md-12">
            <button class="btn btn-solid buttonload" type="submit"  data-dev_remove_id="product_faq_dev_{{ $product_faqs->first() ? $product_faqs->first()->product_id : '' }}" data-form_class="product-order-form-name_{{ $product_faqs->first() ? $product_faqs->first()->product_id : '' }}" data-product_id="{{ $product_faqs->first() ? $product_faqs->first()->product_id : '' }}" id="submit_productfaq">{{__('Submit')}}</button>
        </div>
        
    </div>
  </form>


