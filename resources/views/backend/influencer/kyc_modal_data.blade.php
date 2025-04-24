
<div class="row border-bottom mb-3">
    <div class="col-sm-12">
        <div class="row">
            <div class="col-sm-6">
                @php $front = $influencer_user['kyc']['front_adhar']['proxy_url'] . '400/400' . $influencer_user['kyc']['front_adhar']['image_path']; @endphp
                <img src="{{$front}}" alt="">
            </div>

            <div class="col-sm-6">
                @php $back = $influencer_user['kyc']['back_adhar']['proxy_url'] . '400/400' . $influencer_user['kyc']['back_adhar']['image_path']; @endphp
                <img src="{{$back}}" alt="">
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-6">
        <span>Benefiaciary Name : </span>
        <span>{{$influencer_user['kyc']['account_name']}}</span>
    </div>
    <div class="col-sm-6">
        <span>Bank Name : </span>
        <span>{{$influencer_user['kyc']['bank_name']}}</span>
    </div>
    <div class="col-sm-6">
        <span>Account Number : </span>
        <span>{{$influencer_user['kyc']['account_number']}}</span>
    </div>
    <div class="col-sm-6">
        <span>{{getNomenclatureName('IFSC Code', true)}} : </span>
        <span>{{$influencer_user['kyc']['ifsc_code']}}</span>
    </div>
    <div class="col-sm-6">
        <span>Adhar Number : </span>
        <span>{{$influencer_user['kyc']['adhar_number']}}</span>
    </div>
    <div class="col-sm-6">
        <span>Upi Id : </span>
        <span>{{$influencer_user['kyc']['upi_id']}}</span>
    </div>
   

</div>