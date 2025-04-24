<h4>Rate your Vendor</h4>
<form id="review-driver-form" class="theme-form" action="javascript:void(0)" method="post">
    @csrf
    <input type="hidden" name="vendor_id" value="{{$vendor_id}}">
    <textarea class="form-control" maxlength="500" name="hidden_review" hidden>{{$rating_details->review??''}}</textarea>
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
                    
                </label>
                <input id="rating-4" name="rating" type="radio" value="4"  {{ $rating == 4 ? 'checked' : '' }}/>
                <label for="rating-4" data-value="4">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                </label>
                <input id="rating-3" name="rating" type="radio" value="3"  {{ $rating == 3 ? 'checked' : '' }}/>
                <label for="rating-3" data-value="3">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
              
                </label>
                <input id="rating-2" name="rating" type="radio" value="2"  {{ $rating == 2 ? 'checked' : '' }}/>
                <label for="rating-2" data-value="2">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                </label>
                <input id="rating-1" name="rating" type="radio" value="1"  {{ $rating == 1 ? 'checked' : '' }}/>
                <label for="rating-1" data-value="1">
                    <span class="rating-star">
                        <i class="fa fa-star-o"></i>
                        <i class="fa fa-star"></i>
                    </span>
                </label>
            </div>
        </fieldset>
    </div>

    

    <div class="form-row">
        <span class="text-danger" id="error-msg"></span>
        <span class="text-success" id="success-msg"></span>
        <div class="col-md-12">
            <button class="btn btn-solid" type="submit" id="review_agent_form_button">{{__('Submit Your Review')}}</button>
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
'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
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
url: "{{ route('update-vendor-review')}}",
data: formdata,
cache:false,
// contentType: false,
// processData: false,

success: (data) => {
if(data == 'Success')
    {   
        $('#success-msg').text(data.message);  
        var url = "{{route('vendor.index')}}";
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
