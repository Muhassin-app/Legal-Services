<style>
.rating_question_item .form-group {
    flex-direction: row-reverse;
    display: flex;
    width: auto;
    justify-content: center;
    align-items: center;
}
.rating_question_item .form-group label {
    display: inline-block;
    text-transform: capitalize;
    font-weight: 600;
}
.rating-form .form-legend {
    display: none;
    margin: 0;
    padding: 0;
    font-size: 20px;
    font-size: 2rem;
}
.rating_question_item .rating-form input[type='radio']+label:before{
    right: unset;
    left: 0;
    font-size: 14px;
    top: 50%;
    transform: translateY(-50%);
}
.rating_question_item .rating-form input[type='radio']+label:after{
    right: unset;
    left: 17px;
    font-size: 14px;
    top: 50%;
    transform: translateY(-50%);
}
.rating_question_item  .rating-form .form-item{
    width:140px;
}
.rating_question_item .form-group label:hover i {
    color: gold;
}
.rating_question_item .rating-form .form-item{
    justify-content: flex-start!important;
    display: flex;
}
.rating_question_item .rating-form label .fa{
    font-size: 20px;
    line-height: 20px;
}
.rating_question_item  .rating-form .form-group {
    margin: 0;
}
.radio_cta {
    width: 160px;
    text-align: left;
    display: flex!important;
    flex-wrap: wrap;
}

.radio_cta > .form-group {
    position: relative;
    display: inline-block;
    margin-right: 10px;
    margin-bottom: 0;
}
.radio_cta .form-group label {
    position: relative;
    display: inline-block;
    padding-left: 20px;
    font-size: 12px;
    font-weight: 500;
    color: #000;
    min-width:65px;
}
.radio_cta .form-group label:before {
    content: '';
    width: 16px;
    height: 16px;
    border: 1px solid #000;
    background: transparent;
    display: inline-block;
    position: absolute;
    left: 0;
    top: 10px;
    transform: translateY(-50%);
    border-radius: 3px;
}
.rating_question_item > label {
    width: 60%;
    color: #000;
    font-weight: 500;
    text-transform: capitalize;
    font-size: 14px;
    line-height: 1.5;
}
.radio_cta .form-group label:after {
    content: '';
    width: 10px;
    height: 6px;
    border-left: 2px solid #000;
    border-bottom: 2px solid #000;
    display: inline-block;
    position: absolute;
    left: 3px;
    top: 6px;
    transform: rotate(-45deg);
    opacity: 0;
}
.radio_cta .form-group input{
    position: absolute;
    top: 0;
    left:0;
    right: 0;
    bottom: 0;
    opacity: 0;
    z-index: 1;
    cursor: pointer;
    font-size: 0;
}
.radio_cta .form-group  input:checked ~  label:after{
    opacity: 1;
    border-color: #fff;
}
.radio_cta .form-group  input:checked ~  label:before{
    background-color: var(--theme-deafult);
    border: var(--theme-deafult);
}
.driver_title{
    padding-bottom:15px; 
}
.ratting_textarrea label{
    color: #000;
    font-weight: 500;
    text-transform: capitalize;
    font-size: 14px;
    line-height: 1.5;
}
.ratting_textarrea textarea {
    resize: none;
    background: #f5f5f5;
    border: 1px solid #f5f5f5;
    height: 120px;
    color: #000;
    padding: 10px;
    margin-top: 10px;
}
.al_body_template_one  .ratting_textarrea button {
    width: 100%!important;
    display: block!important;
    max-width: 100%!important;
    letter-spacing: 0;
    font-size: 14px!important;
}






.rating-form .form-group {
    position: relative;
    border: 0
}

.rating-form .form-legend {
    display: none;
    margin: 0;
    padding: 0;
    font-size: 20px;
    font-size: 2rem
}

.rating-form .form-item {
    position: relative;
    width: 220px;
    direction: rtl
}

.rating-form .form-legend+.form-item {
    padding-top: 10px
}

.rating-form input[type='radio'] {
    position: absolute;
    left: -9999px
}

.rating-form label {
    display: inline-block;
    cursor: pointer;
    margin: 0
}

.rating-form .rating-star {
    display: inline-block;
    position: relative
}

.rating-form input[type='radio']+label:before,
.rating-form input[type='radio']+label:after {
    top: 13px;
    font-size: 16px
}

.rating-form input[type='radio']+label:before {
    content: attr(data-value);
    position: absolute;
    right: 30px;
    opacity: 0;
    direction: ltr
}

.rating-form input[type='radio']:checked+label:before {
    right: 25px;
    opacity: 1
}

.rating-form input[type='radio']+label:after {
    content: "/ 5";
    position: absolute;
    right: 0;
    opacity: 0;
    direction: ltr
}

.rating-form input[type='radio']:checked+label:after {
    opacity: 1
}

.rating-form label .fa {
    font-size: 30px;
    line-height: 30px
}

.rating-form label:hover .fa-star-o,
.rating-form label:focus .fa-star-o,
.rating-form label:hover~label .fa-star-o,
.rating-form label:focus~label .fa-star-o,
.rating-form input[type='radio']:checked~label .fa-star-o {
    opacity: 0
}

.rating-form label .fa-star {
    position: absolute;
    left: 0;
    top: 0;
    opacity: 0
}

.rating-form label:hover .fa-star,
.rating-form label:focus .fa-star,
.rating-form label:hover~label .fa-star,
.rating-form label:focus~label .fa-star,
.rating-form input[type='radio']:checked~label .fa-star {
    opacity: 1
}

.rating-form input[type='radio']:checked~label .fa-star {
    color: gold
}

.rating-form .ir {
    position: absolute;
    left: -9999px
}

.rating-form .form-action {
    opacity: 0;
    position: absolute;
    left: 5px;
    bottom: 0
}

.rating-form input[type='radio']:checked~.form-action {
    cursor: pointer;
    opacity: 1
}

body .rating-form .btn-reset {
    display: inline-block;
    margin: 0;
    padding: 4px 10px;
    border: 0;
    font-size: 16px;
    background: #fff;
    color: #333;
    cursor: auto;
    border-radius: 5px;
    outline: 0
}

.rating-form .btn-reset:hover,
.rating-form .btn-reset:focus {
    background: gold
}

.rating-form input[type='radio']:checked~.form-action .btn-reset {
    cursor: pointer
}

.rating-form .form-output {
    display: none;
    position: absolute;
    right: 15px;
    bottom: -45px;
    font-size: 30px;
    font-size: 3rem;
    opacity: 0
}

.no-js .rating-form .form-output {
    right: 5px;
    opacity: 1
}

.rating-form input[type='radio']:checked~.form-output {
    right: 5px;
    opacity: 1
}
 .ratting_textarrea button {
    width: 100%!important;
    display: block!important;
    max-width: 100%!important;
    letter-spacing: 0;
    font-size: 14px!important;
}
</style>



<div class="driver_title">
    <h4>Rate your Driver</h4>
</div>
<form id="review-driver-form" class="theme-form" action="javascript:void(0)" method="post">
    @csrf
    <input type="hidden" name="order_vendor_product_id" value="{{$order_vendor_product_id}}">
    <input type="hidden" name="file_set" id="files_set" value="0">
    <input type="hidden" name="only_set_radio" id="only_set_radio" value="0">
    <input type="hidden" name="dispatch_traking_url" id="dispatch_traking_url" value="{{ $dispatch_traking_url }}">


    <textarea class="form-control" maxlength="500" name="hidden_review" hidden>{{$rating_details->review??''}}</textarea>
    @if(!empty($dispatch_rating_ques))
    <div id="rating_question" class="form-row ">
     
            @foreach($dispatch_rating_ques as $question)
            <div class="col-md-12 mb-3 p-0" id="{{$question['id']}}Input">
                <div class="row w-100  justify-content-between m-0 rating_question_item">
                    <label for="">{{$question['title'] ? $question['title']  : ''}}</label>
                     <input type="hidden" name="question_id[]" value="{{ $question['id'] }}">
                    @if(($question['type']) == 5)
                        <div class="d-flex justify-space-between radio_cta">
                            @foreach($question['option'] as $key => $option)
                            @php
                            $selected = (isset($question['order_quetions']) && !empty($question['order_quetions'])) ? $question['order_quetions']['option_id'] : '';
                            $checked = ($selected == $option['id']) ? 'checked': (($key ==0 ) ? 'checked' : '' );
                            @endphp
                            <div class="form-group ">
                                <input type="radio" id="option_{{ $option['id']}}" name="option_id_{{ $question['id'] }}" {{ $checked }} value="{{ $option['id'] }}">
                            <label for="option_{{ $option['id']}}">{{  $option['title'] }}</label>
                            </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
            @endforeach
    </div>
    @endif
    @if(!empty($dispatch_rating_types))
    <div id="rating_types" class="form-row">
        <input type="hidden" name="rating_types" value='1'>
        @foreach($dispatch_rating_types as $rating_type)
        <div class="col-md-12 mb-3 p-0" id="{{$rating_type['id']}}Input">
            <div class="row w-100 align-items-center justify-content-between m-0 rating_question_item">
                <label for="">{{$rating_type['title'] ? $rating_type['title']  : ''}}</label>
                 <input type="hidden" name="rating_type_id[]" value="{{ $rating_type['id'] }}">
                 @php
                 $dispatch_rating = (isset($rating_type['order_rating']) && !empty($rating_type['order_rating'])) ? $rating_type['order_rating']['rating'] : 0;
                 @endphp
                 @include('frontend.modals.driverRatingStar')
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @if(empty($dispatch_rating_types))
    <div class="rating-form">
        <fieldset class="form-group">
            <legend class="form-legend">Rating:</legend>
            <div class="form-item">

            <input id="rating-5" name="rating" type="radio" value="5" {{ $rating == 5 ? 'checked' : '' }}/>
                <label for="rating-5" data-value="5">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="ir">5</span>
                </label>
                <input id="rating-4" name="rating" type="radio" value="4"  {{ $rating == 4 ? 'checked' : '' }}/>
                <label for="rating-4" data-value="4">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="ir">4</span>
                </label>
                <input id="rating-3" name="rating" type="radio" value="3"  {{ $rating == 3 ? 'checked' : '' }}/>
                <label for="rating-3" data-value="3">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="ir">3</span>
                </label>
                <input id="rating-2" name="rating" type="radio" value="2"  {{ $rating == 2 ? 'checked' : '' }}/>
                <label for="rating-2" data-value="2">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="ir">2</span>
                </label>
                <input id="rating-1" name="rating" type="radio" value="1"  {{ $rating == 1 ? 'checked' : '' }}/>
                <label for="rating-1" data-value="1">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                    <span class="ir">1</span>
                </label>

                <div class="form-output">
                    ? / 5
                </div>

            </div>
        </fieldset>
    </div>
    @endif
    

    <div class="form-row ratting_textarrea">

        <div class="col-md-12 mb-3">
            <label for="review">{{__('Review')}}</label>
            <textarea class="form-control"
                placeholder="{{__('Write Your Testimonial Here')}}"
                id="exampleFormControlTextarea1" rows="4"  name="review" maxlength="500">{{$rating_details->review??''}}</textarea>
        </div>
        <span class="text-danger" id="error-msg"></span>
        <span class="text-success" id="success-msg"></span>
        <div class="col-md-12">
            <button class="btn btn-solid buttonload" type="submit" id="review_agent_form_button">{{__('Submit Your Review')}}</button>
        </div>

    </div>
  </form>



<script type="text/javascript">
$(document).ready(function (e) {
    // $('body').delegate('.local-img-del','click',function() {
    //     var img_id = $(this).data('id');
    //     $(this).prev().remove();
    //     $(this).remove();
    //     $("#"+img_id).remove();
    // });


  $('input[type=radio][name=rating]').on('change', function() {
    $('#only_set_radio').val(1);
    $('.rating_files').show();
    $('.form-row').show();
    // $(this).closest("form").submit();
    });


    $('#review_agent_form_button').on('click',function(e){
        $('#only_set_radio').val(0);
        $(this).closest("form").submit();
    });


$.ajaxSetup({
headers: {
'X-CSRF-TOKEN': $('meta[name="  -token"]').attr('content')
}
});

$('#review-driver-form').submit(function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    var formdata = $('#review-driver-form').serialize();
    //alert(formdata);

    let review = $('#exampleFormControlTextarea1').val();
    $.ajax({
    type:'POST',
    url: "{{ route('update.driver.rating')}}",
    data: formdata,
    cache:false,
    // contentType: false,
    // processData: false,

    success: (data) => {
    if(data.status == 'Success')
        {   
            $('#success-msg').text(data.message);  
            var url = "{{route('user.orders',['pageType' => 'pastOrders'])}}";
            $(location).prop('href', url);           
        }else{
            $('#error-msg').text(data.message);
            $("#review_agent_form_button").html('Submit Your Review').prop('disabled', false);
        }
    },
    error: function(data){
        $('#error-msg').text(data.message);
        $("#review_agent_form_button").html('Submit Your Review').prop('disabled', false);
    }
});




});

});
</script>
