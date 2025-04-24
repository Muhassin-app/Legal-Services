
 @foreach($giftcardList as $active_giftcard)
 <div class="col-lg-12 mt-2">
    <div class="coupon-code mt-0">
        <div class="row p-2">
            <div class="col-5">
                <img class='w-100' src="{{$active_giftcard->giftCard->image['proxy_url'].'100/50'.$active_giftcard->giftCard->image['image_path']}}" alt="">
            </div>
            <div class="col-7">
                <h4 class="mt-0">{{ $active_giftcard->giftCard->title }}</h4>
                <p>{{ $active_giftcard->giftCard->short_desc }}</p>
                <div class="d-flex align-items-center justify-content-between">
                    <small class="mr-2">{{ __('Expired On') }}</small>
                    <span class="text-success">{{ dateTimeInUserTimeZone($active_giftcard->giftCard->expiry_date,  (auth()->user()->timezone ?? 'Asia/Kolkata'), true, false) }}</span>
                </div>

            </div>
        </div>
        <hr class="m-0">
        <div class="code-outer p-2 text-uppercase d-flex align-items-center justify-content-between">
            <label class="m-0">{{ $active_giftcard->giftCard->name }}</label>
            <a class="btn btn-solid apply_gifCard_code_btn"  data-giftCard_id="{{ $active_giftcard->giftCard->id }}"  style="cursor: pointer;">({{ $active_giftcard->giftCard->amount }})Apply</a>
        </div>
        <hr class="m-0">
    </div>
</div>
@endforeach