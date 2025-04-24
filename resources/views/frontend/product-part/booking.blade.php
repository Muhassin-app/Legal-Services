<style>
  .alRentalSinglePageView .single_product-input input {
      width: 48%;
      display: inline-block;
      border: none;
      height: auto;
      padding: 30px 0px 10px 4px;
      font-size: 13px;
  }
  .alRentalSinglePageView .single_product-input {
      border: 1px solid#cfc9c9;
      width: 56%;
      border-radius: 5px;
      position: relative;
  }
  .alRentalSinglePageView .single_cart-temp_label{
    width: 56%;
    position: absolute;
    z-index: 1;
  }
  .alRentalSinglePageView .single_cart-temp_label label{
      display: inline-block;
      width: 46%;
      font-size: 12px;
      padding: 6px 0px 0px 6px;
      color: #000;
  }
  
  .alRentalSinglePageView .single_product-input input:nth-child(1) {
    border-right: 1px solid#cfc9c9;
      border: 1px solid#cfc9c9;
      border-top: none;
      border-bottom: none;
      border-left: none;
  }
  .disclaimer{
      font-style: italic;
  }
  </style>
  <div class="alRentalSinglePageView">
    <div class="addManualTime">
      <div class="addManualTimeGroup" style="text-align:left;">
        <div class="single_cart-temp_label">
          <label class="text-left mb-0">Start/Date Time</label>
          <label class="text-left mb-0">End/Date Time</label>
        </div>
          <div class="single_product-input mb-2">
            <input id="blocktime" class="form-control" autofocus>
            <input id="blocktime2" class="form-control">
          </div>
      </div>
  
    </div>
    
    <div id="product_variant_additional_increment_wrapper" >
      <div class="product-description border-product pb-0">
          {{-- <h6 class="product-title mt-0">{{__('Extended duration By('.@$product->additional_increments.'hr:'.@$product->additional_increments_min.'min/'.Session::get('currencySymbol').number_format(@$product->variant[0]->incremental_price * @$product->variant[0]->multiplier,2,".",",").')')}}:
        </h6> --}}
        <div class="mt-0">{{__('Duration') }}:
        
          <div class="qty-box mb-3">
              <div class="input-group">
                    {{-- <span class="input-group-prepend">
                        <button type="button" class="btn incremental-left-minus" data-type="minus" data-field="" data-batch_count={{$product->batch_count}} data-minimum_order_count={{$product->minimum_order_count}}><i class="ti-angle-left"></i>
                        </button>
                    </span> --}}
                    <input style="display: none" readonly  step="{{$product->additional_increments*60+$product->additional_increments_min}}" type="number" min="0" name="incremental_hrs"  onkeypress="return event.charCode > 47 && event.charCode < 58;" pattern="[0-9]{5}" id="incremental_hrs" class="form-control input-qty-number incremental_hrs"  value="{{$product->additional_increments*60+$product->additional_increments_min}}">
    
                    <input  type="hidden" min="0" name="total_hrs" id="total_hrs" value="{{getMinutes($product->minimum_duration,$product->minimum_duration_min)}}" >
    
                    <input style="width: 135px" readonly type="text"  id="incremental_hrs_hidden" class="form-control input-qty-number"  value="{{$product->minimum_duration.' hour '.$product->minimum_duration_min. ' min'}}">
                    <input  type="hidden"  name="first_variant" id="first_variant" value="{{@$product->variant[0]->id}}" >
                    {{-- <span class="input-group-prepend quant-plus">
                        <button type="button" class="btn incremental-right-plus" data-type="plus" data-field="">
                            <i class="ti-angle-right"></i>
                        </button>
                    </span> --}}
                </div>
            </div>
  
          <div class="mt-0">
            <div class="duration">
              <div class="total_duration d-flex align-items-center">
                {{Session::get('currencySymbol')}}
                <b class="total_price m-0 pr-1">
                  {{number_format(@$product->variant[0]->price * @$product->variant[0]->multiplier,2,".",",")}}
                </b> {{__("for first")}}
                <b class="m-0 px-1 min_hrs">{{ @$product->minimum_duration}}</b> {{__("hour")}}
                <b class="m-0 px-1 min_min">{{@$product->minimum_duration_min}}</b> {{__("min")}}
              </div>
            </div>
          </div>
          <div class="disclaimer mb-3">
            <span class="d-flex align-items-center">
  
                {{__("Extra duration will be charged")}}
  
                <b class="px-1 variant_incremental_price">{{Session::get('currencySymbol')}}{{number_format(@$product->variant[0]->incremental_price * @$product->variant[0]->multiplier,2,".",",")}} </b>
              {{__("per")}}
                <b class="px-1 addtional_hrs">{{ ($product->additional_increments) }}</b> {{__("hour")}} <b class="px-1 addtional_min">{{($product->additional_increments_min)}} </b>
              {{__("min")}}
  
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
  
  
  
  @section('script-bottom-js')
  
      {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js" ></script> --}}
    {{-- <script src="{{ asset('assets/js/backend/product/productSchedule.js')}}"></script> --}}
  
    <script type="text/javascript">
    function padTo2Digits(num) {
      return num.toString().padStart(2, '0');
    }
      function timeConvertCal(hr,min){
       //console.log(hr);
       // //console.log(min);
        return (parseInt(hr)*parseInt(60)+parseInt(min));
      }
      function timeToHrMinConvertCal(totalMinutes){
        var hours = Math.floor(totalMinutes / 60);
        var minutes = totalMinutes % 60;
        return padTo2Digits(hours) + ' hour ' + padTo2Digits(minutes) + ' min';
      }
      let product_variant_data  =  [];
      var default_minutes = incremental_price = actual_price = default_currency = default_step = base_hours_min ='' ;
      var min_dur_hrs = min_dur_min = additional_base_hr = additional_base_min =  total_min = '';
      $(function(e) {
          total_min = default_minutes =  timeConvertCal('{{$product->minimum_duration}}','{{$product->minimum_duration_min}}');
          base_hours_min = timeToHrMinConvertCal(default_minutes);
          incremental_price = '{{@$product->variant[0]->incremental_price}}';
         actual_price = '{{@$product->variant[0]->actual_price}}';
         default_currency = "{{Session::get('currencySymbol')}}";
         default_step = timeConvertCal('{{$product->additional_increments}}','{{$product->additional_increments_min}}');
  
         min_dur_hrs = '{{ $product->minimum_duration }}';
         min_dur_min = '{{ $product->minimum_duration_min }}';
         additional_base_hr = '{{$product->additional_increments}}';
         additional_base_min = '{{$product->additional_increments_min}}';
  
          var selectedStartDate = ''; // selected start
          var selectedEndDate = ''; // selected end
          var currentDate = moment().format("M/DD/YY hh:mm A");
          $checkinInput = $('#blocktime');
          $checkoutInput = $('#blocktime2');
          $checkinInput.val( moment().format("M/DD/YY hh:mm A"));
          $checkoutInput.val(moment().add(min_dur_hrs,'hours').add(min_dur_min,'minutes').format("M/DD/YY hh:mm A"));
          $(".incremental-left-minus").on("click", function() {
              document.getElementById('incremental_hrs').stepDown();
              var incremental_hrs = document.getElementById('incremental_hrs').value;
              var start_current_time = $($checkinInput).val();
              var end_current_time = $($checkoutInput).val();
              total_min = document.getElementById('total_hrs').value;
  
              if(incremental_hrs > 0) {
                $checkoutInput.val(moment(end_current_time).add(incremental_hrs,'minutes').format("M/DD/YY hh:mm A"));
                var checkOutPicker = $checkoutInput.data('daterangepicker');
                checkOutPicker.setEndDate(moment(end_current_time).add(incremental_hrs,'minutes').format("M/DD/YY hh:mm A"));
  
                var checkInPicker = $checkinInput.data('daterangepicker');
  
                checkInPicker.setEndDate(moment(end_current_time).add(incremental_hrs,'minutes').format("M/DD/YY hh:mm A"));
  
                var formData = {
                  variant_option_id:$('.changeVariant:checked').val(),
                  product_id:$("input[name='product_id']").val(),
                  selectedStartDate:start_current_time,
                  selectedEndDate:end_current_time
                }
                total_min = ((parseInt(total_min))-(parseInt(default_step)));
              } else {
  
                $checkoutInput.val(moment().add(min_dur_hrs,'hours').add(min_dur_min,'minutes').format("M/DD/YY hh:mm A"));
                var checkOutPicker = $checkoutInput.data('daterangepicker');
                checkOutPicker.setEndDate(moment().add(min_dur_hrs,'hours').add(min_dur_min,'minutes').format("M/DD/YY hh:mm A"));
  
                var checkInPicker = $checkinInput.data('daterangepicker');
  
                checkInPicker.setEndDate(moment().add(min_dur_hrs,'hours').add(min_dur_min,'minutes').format("M/DD/YY hh:mm A"));
  
                var formData = {
                  variant_option_id: $('.changeVariant:checked').val(),
                  product_id:$("input[name='product_id']").val(),
                  selectedStartDate:start_current_time,
                  selectedEndDate:end_current_time
                }
                total_min = parseInt(default_minutes);
              }
              $('#incremental_hrs_hidden').val(timeToHrMinConvertCal(total_min));
              $('#total_hrs').val(total_min);
              check_product_availibility(formData);
  
          });
  
          $(".incremental-right-plus").on("click", function() {
            document.getElementById('incremental_hrs').stepUp();
            //setTimeout(() => {
              var incremental_hrs = document.getElementById('incremental_hrs').value;
  
  
              var end_current_time = $($checkoutInput).val();
  
              var start_current_time = $($checkinInput).val();
              var end_current_time = $($checkoutInput).val();
  
              $checkoutInput.val(moment(end_current_time).add(default_step,'minutes').format("M/DD/YY hh:mm A"));
              var checkOutPicker = $checkoutInput.data('daterangepicker');
  
              checkOutPicker.setEndDate(moment(end_current_time).add(default_step,'minutes').format("M/DD/YY hh:mm A"));
  
                var checkInPicker = $checkinInput.data('daterangepicker');
  
                checkInPicker.setEndDate(moment(end_current_time).add(default_step,'minutes').format("M/DD/YY hh:mm A"));
  
                var formData = {
                  variant_option_id:$('.changeVariant:checked').val(),
                  product_id:$("input[name='product_id']").val(),
                  selectedStartDate:start_current_time,
                  selectedEndDate:end_current_time
                }
  
              // console.log($($checkoutInput).val());
              // console.log(document.getElementById('incremental_hrs').value);
  
               var total1=   $('#total_hrs').val();
                console.log('total',total_min);
                console.log('incremental_hrs',default_step);
                total_min = parseInt(total1)+parseInt(default_step);
  
                $('#total_hrs').val(total_min);
  
                $('#incremental_hrs_hidden').val(timeToHrMinConvertCal(total_min));
          });
  
  
          $('#blocktime, #blocktime2').daterangepicker({
              locale: {
                    format: 'M/DD/YY hh:mm A'
              },
              timePicker: true,
              startDate: moment(),
              endDate: moment().add(min_dur_hrs,'hours').add(min_dur_min,'minutes'),
              minDate:new Date(),
              //"alwaysShowCalendars": true,
              // "minDate": currentDate,
              // "maxDate": moment().add('months', 1),
              autoApply: true,
              autoUpdateInput: false
          }, async function(start, end, label) {
             selectedStartDate = start.format('M/DD/YY hh:mm A'); // selected start
             selectedEndDate = end.format('M/DD/YY hh:mm A'); // selected end
  
            // Updating Fields with selected dates
            $checkinInput.val(selectedStartDate);
            $checkoutInput.val(selectedEndDate);
  
            // Setting the Selection of dates on calender on CHECKOUT FIELD (To get this it must be binded by Ids not Calss)
            var checkOutPicker = $checkoutInput.data('daterangepicker');
            checkOutPicker.setStartDate(selectedStartDate);
            checkOutPicker.setEndDate(selectedEndDate);
  
            // Setting the Selection of dates on calender on CHECKIN FIELD (To get this it must be binded by Ids not Calss)
            var checkInPicker = $checkinInput.data('daterangepicker');
            checkInPicker.setStartDate(selectedStartDate);
            checkInPicker.setEndDate(selectedEndDate);
            ////console.log(selectedEndDate);
            $('.incremental_hrs').val(0);
            $('#incremental_hrs_hidden').val(base_hours_min);
            var formData = {
              variant_option_id:$('.changeVariant:checked').val(),
              product_id:$("input[name='product_id']").val(),
              selectedStartDate:selectedStartDate,
              selectedEndDate:selectedEndDate
            }
            await calculateExtraTimeforproduct(selectedStartDate,selectedEndDate);
  
             check_product_availibility(formData);
  
  
          });
  
          async function check_product_availibility(formData){
            if(formData.variant_option_id == undefined){
              formData.variant_option_id = '';
            }
            formData.variant_id = $('#first_variant').val()
              axios.post(`/booking/checkProductAvailibility`, formData)
              .then(async response => {
              //console.log(response);
                  var data = response.data.variant_data;
                  if(response.data.success){
                    if(!data.variant_product_quantity){
                    $("a#add_to_cart_btn").removeClass("addToCart");
                    await sweetAlert.error('',_language.getLanString('Not available yet!')); 
                    return false;
                    }
  
                    var available_product_variant = data.available_product_variant;
                    var end_time = data.end_time;
                    var start_time = data.start_time;
                    if(available_product_variant) {
                      $("a#add_to_cart_btn").addClass("addToCart");
                      //console.log( data);
                      $('#available_product_variant').val(available_product_variant);
                      $('#start_time').val(start_time);
                      $('#end_time').val(end_time);
                      product_variant_data = data.product_variant_data;
                      if(product_variant_data) {
                        var incremental_hrs = document.getElementById('incremental_hrs').value;
                        populateProductData(product_variant_data);
                        calculation(incremental_hrs,product_variant_data.incremental_price,product_variant_data.incremental_price_per_min);
                        if(product_variant_data.check_if_in_cart.length > 0){
                            product_variant_data.check_if_in_cart.map(checkIfInCart);
                          //checkIfInCart(product_variant_data.check_if_in_cart);
                        } else {
                          localStorage.setItem('in_cart','false');
                        }
                      }
                    } else {
                      $("a#add_to_cart_btn").removeClass("addToCart");
                      Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Already booked, Please select diffrent slot!',
                      })
                    }                 
                  } 
                  // else{
                  //   Swal.fire({
                  //       icon: 'error',
                  //       title: 'Oops...',
                  //       text: 'Something went wrong, try again later!',
                  //     })
                  // }
              })
              .catch(e => {
                //console.log(e);
                  Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong, try again later!',
                  })
              })
          }
  
          function calculation(incremental_min,incremental_price,incremental_price_per_min){
              var total_additional_price = NumberFormatHelper.formatPrice((incremental_min/incremental_price_per_min));
             // //console.log(total_additional_price);
              var total_minutes =  (parseInt(default_minutes)+parseInt(incremental_min));
              var total_calculated_price =  (parseInt(actual_price)+parseInt(total_additional_price));
              //$('.total_duration').html(total_minutes);
              //$('.total_price').html(NumberFormatHelper.formatPrice(total_calculated_price));
  
          }
  
          function populateProductData(productData){
              if(productData){
                actual_price = NumberFormatHelper.formatPrice(productData.actual_price);
                incremental_price = productData.incremental_price;
                $('.product_fixed_price').html(actual_price);
                default_minutes = timeConvertCal(productData.product.minimum_duration,productData.product.minimum_duration_min);
                default_step = timeConvertCal(productData.product.additional_increments,productData.product.additional_increments_min)
                //$('.total_duration').html(default_minutes);
                $('.total_price').html(actual_price);
                $('.min_hrs').html(productData.product.minimum_duration);
                $('.min_min').html(productData.product.minimum_duration_min);
                $('.addtional_hrs').html(productData.product.additional_increments);
                $('.addtional_min').html(productData.product.additional_increments_min);
                $('.variant_incremental_price').html(productData.incremental_price);
                //$('.disclaimer').html(`<p>(Extra minutes will be calculated in multiple of ${default_currency}${incremental_price} per ${default_step} min)</p>`);
                $("#incremental_hrs").attr('step', default_step);
              }
          }
  
          function init(){
            $('.incremental_hrs').val(0);
            $('#incremental_hrs_hidden').val(base_hours_min);
            var formData = {
              variant_option_id:$('.changeVariant:checked').val(),
              product_id:$("input[name='product_id']").val(),
              selectedStartDate:$('#blocktime').val(),
              selectedEndDate:$('#blocktime2').val()
            }
            check_product_availibility(formData);
          }
  
          function diff_minutes(dt2, dt1) {
            var start_date = new Date(dt2);
            var end_date = new Date(dt2);
            //console.log(dt2);
            //console.log(dt1);
            return Math.abs(new Date(dt2) - new Date(dt1))/60000;
            // var diff =(end_date.getTime() - start_date.getTime()) / 1000;
            // diff /= 60;
            // return Math.abs(Math.round(diff));
  
          }
          function calculateExtraTimeforproduct(selectedStartDate,selectedEndDate){
            var total_sel_min = diff_minutes(selectedStartDate,selectedEndDate);
            console.log("asdfasdfasdf");
            // console.log(parseInt(default_minutes));
            //console.log(parseFloat(total_sel_min) - Number(default_minutes));
            var remaining = parseFloat(total_sel_min) - Number(default_minutes);
            //default_step
            var divide = parseInt(remaining)/default_step;
            divide = Math.floor(divide);
            var reminder = parseInt(remaining)%default_step;
            if(reminder > 0){
              divide = parseInt(divide) + 1;
            }
            var extra_t_min = parseInt(default_step)*parseInt(divide);
  
            $('#incremental_hrs').val(extra_t_min);
            var t_min_hr_min = parseInt(extra_t_min)+parseInt(default_minutes);
            if(t_min_hr_min< default_minutes){
              t_min_hr_min = default_minutes;
              // $checkoutInput = $('#blocktime2');
              // $checkinInput = $('#blocktime');
              
              
              // var checkOutPicker = $checkoutInput.data('daterangepicker');
              // var checkInPicker = $checkinInput.data('daterangepicker');
              //   $checkoutInput.val(moment().add(default_minutes,'minutes').format("M/DD/YY hh:mm A"));
              //   $checkinInput.val(moment().add(default_minutes,'minutes').format("M/DD/YY hh:mm A"));
              //   checkOutPicker.setEndDate(moment().add(default_minutes,'minutes').format("M/DD/YY hh:mm A"));
              //   checkInPicker.setEndDate(moment().add(default_minutes,'minutes').format("M/DD/YY hh:mm A"));
                
            }
  
            $('#incremental_hrs_hidden').val(timeToHrMinConvertCal(t_min_hr_min));
            $('#total_hrs').val(parseInt(t_min_hr_min));
  
             console.log(parseInt(t_min_hr_min)+parseInt(default_minutes));
  
          }
  
  
          function checkIfInCart(v_p) {
            localStorage.setItem('in_cart','false');
              if(v_p.variant_id == $('#prod_variant_id').val()){
                  localStorage.setItem('in_cart','true');
              }
  
          }
          init();
  
        });
    </script>
  @endsection