<hr class="my-2" style="width:100%">
@php
$LongTermService  = $product->LongTermService;
@endphp
<div class="row mb-1 pl-2 d-flex align-items-center LongTermProduct">
    <div class="product">
        <h6 class="product-title mb-0">{{ __('Service Product') }}:
            <span class="ml-1">
              {{ !empty($LongTermService->product) && isset($LongTermService->product->translation_one) ? $LongTermService->product->translation_one->title : (@$LongTermService->product->title ?? "na")  }}
            </span>
        </h5>
        <div class="d-flex justify-content-between align-items-center">
            <h6 class="product-title mt-0">{{ __('No. of Bookings') }}:
                <span class="ml-1">
                    {{ !empty($LongTermService) ? $LongTermService->service_quentity : '' }}
                </span>
            </h6>
        </div>   
    </div>
</div> 