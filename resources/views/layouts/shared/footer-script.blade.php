<!-- bundle -->
<!-- Vendor js -->
<?php
    $theme = \App\Models\ClientPreference::where(['id' => 1])->first();
    $analytics = getAdditionalPreference(['gtag_id', 'fpixel_id']);
if (Session::has('toaster')) {
    $toast = Session::get('toaster');
    echo '<script>
            $(document).ready(function(){
                $.NotificationApp.send("' . $toast["title"] . '", "' . $toast["body"] . '", "top-right", "' . $toast["color"] . '", "' . $toast["type"] . '");
            });
        </script>';
}
?>
<script src="{{asset('assets/libs/moment/moment.min.js')}}"></script>

<script src="https://unpkg.com/axios/dist/axios.min.js"></script>
<script src="{{asset('assets/libs/selectize/selectize.min.js')}}"></script>
<script src="{{asset('assets/libs/mohithg-switchery/mohithg-switchery.min.js')}}"></script>
<script src="{{asset('assets/libs/multiselect/multiselect.min.js')}}"></script>
<script src="{{asset('assets/libs/select2/select2.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-select/bootstrap-select.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-touchspin/bootstrap-touchspin.min.js')}}"></script>
<script src="{{asset('assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js')}}"></script>
<script src="{{asset('assets/libs/flatpickr/flatpickr.min.js')}}"></script>
<script src="{{asset('front-assets/js/underscore.min.js')}}"></script>
<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<script src="{{asset('front-assets/js/jquery-ui.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-colorpicker/bootstrap-colorpicker.min.js')}}"></script>
<script src="{{asset('assets/libs/clockpicker/clockpicker.min.js')}}"></script>
<script src="{{asset('assets/libs/bootstrap-datepicker/bootstrap-datepicker.min.js')}}"></script>
<script src="{{asset('assets/libs/devbridge-autocomplete/devbridge-autocomplete.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>
<script src="{{asset('assets/js/pages/my-form-advanced.init.js')}}"></script>
<script src="{{asset('assets/libs/jquery-toast-plugin/jquery-toast-plugin.min.js')}}"></script>
<script src="{{asset('assets/js/pages/toastr.init.js')}}"></script>
<script src="{{asset('assets/libs/datatables/datatables.min.js')}}"></script>
<script src="https://cdn.socket.io/4.1.2/socket.io.min.js" integrity="sha384-toS6mmwu70G0fw54EGlWWeA4z3dyJ+dlXBtSURSKN4vyRFOcxd3Bzjj/AoOwY+Rg" crossorigin="anonymous"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-app.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.3.2/firebase-messaging.js"></script>
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.20/jquery.datetimepicker.full.min.js" ></script> --}}
<script src="{{asset('assets/libs/datetimepicker/daterangepicker.min.js')}}" ></script>
<script src="{{ asset('assets/js/alert/alert.js') }}"></script>
<script src="{{asset('assets/js/backend/backend_common.js')}}"></script>


{{-- <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js" ></script> --}}
{{-- add translation file  --}}
@include('layouts.language')
@yield('headerJs')
{{-- end translation file  --}}

{{-- <script src="{{asset('assets/libs/datetimepicker/jquery.datetimepicker.min.js')}}"></script> --}}
@if((!empty($socket_url)))
    <script src="{{$socket_url}}/socket.io/socket.io.js"></script>
@endif

<script>
    
    let stripe_publishable_key = "{{ $stripe_publishable_key }}";
    let is_hyperlocal = 0;
    var business_type = '';

    @if($client_preference_detail)
        @if((isset($client_preference_detail->is_hyperlocal)) && ($client_preference_detail->is_hyperlocal == 1))
            is_hyperlocal = 1;
        @endif

        @if((isset($client_preference_detail->business_type)) && ($client_preference_detail->business_type != ''))
            business_type = "{{$client_preference_detail->business_type}}";
        @endif
    @endif
    var base_url = "{{ url('/')}}";

    function gm_authFailure() {
        $('.excetion_keys').append('<span><i class="mdi mdi-block-helper mr-2"></i> <strong>Google Map</strong> key is not valid</span><br/>');
        $('.displaySettingsError').show();
    };

    const startLoader = function(element) {
        // check if the element is not specified
        if (typeof element == 'undefined') {
            element = "body";
        }
        // set the wait me loader
        $(element).waitMe({
            effect: 'bounce',
            text: 'Please Wait..',
            bg: 'rgba(255,255,255,0.7)',
            //color : 'rgb(66,35,53)',
            color: '#EFA91F',
            sizeW: '20px',
            sizeH: '20px',
            source: ''
        });
    }

    function isNumberKey(evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {

            return false;
        }
        return true;
    }

    const stopLoader = function(element) {
        // check if the element is not specified
        if (typeof element == 'undefined') {
            element = 'body';
        }
        // close the loader
        $(element).waitMe("hide");
    }
</script>
@if(!str_contains(url()->current(), '/godpanel'))
@if((!empty(Auth::user())))
@if((!empty($socket_url)))
    <script>
        //createSocketConnection();
    </script>
@endif
<script>
     //createSocketConnection();
      $(document).ready( async function() {
       
        // Audio.prototype.play = (function(play) {

        //     return function() {
        //         var audio = this,
        //             args = arguments,
        //             promise = play.apply(audio, args);
        //             console.log('as');
        //         if (promise !== undefined) {
        //             promise.catch(_ => {
        //                 // Autoplay was prevented. This is optional, but add a button to start playing.
        //                 var el = document.createElement("button");
        //                 el.innerHTML = "Play";
        //                 el.addEventListener("click", function() {
        //                     play.apply(audio, args);
        //                 });
        //                 this.parentNode.insertBefore(el, this.nextSibling)
        //             });
        //         }
        //     };
        // })(Audio.prototype.play);
    //     var x = document.getElementById("orderAudio");
    //     console.log(x);
    //    x.play();
          //alert('hllo');
         //get_latest_order_socket('54855119');
      });
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });

    function get_reached_vendor_location_socket(order_number){
        Audio.prototype.play = (function(play) {
            return function() {
                var audio = this,
                    args = arguments,
                    promise = play.apply(audio, args);
                if (promise !== undefined) {
                    promise.catch(_ => {
                        // Autoplay was prevented. This is optional, but add a button to start playing.
                        var el = document.createElement("button");
                        el.innerHTML = "Play";
                        el.addEventListener("click", function() {
                            play.apply(audio, args);
                        });
                        this.parentNode.insertBefore(el, this.nextSibling)
                    });
                }
            };
        })(Audio.prototype.play);
        var x = document.getElementById("orderAudio");
        x.play();
        $("#reached_location_new_order #orderNo").html(order_number)
        $("#reached_location_new_order").modal('show');
    }
    
    function get_latest_order_socket(order_number){
        Audio.prototype.play = (function(play) {
            return function() {
                var audio = this,
                    args = arguments,
                    promise = play.apply(audio, args);
                if (promise !== undefined) {
                    promise.catch(_ => {
                        // Autoplay was prevented. This is optional, but add a button to start playing.
                        var el = document.createElement("button");
                        el.innerHTML = "Play";
                        el.addEventListener("click", function() {
                            play.apply(audio, args);
                        });
                        this.parentNode.insertBefore(el, this.nextSibling)
                    });
                }
            };
        })(Audio.prototype.play);
        var x = document.getElementById("orderAudio");
        x.play();
        $.ajax({
            url: "{{ route('orders.filter') }}",
            type: "POST",
            dataType: "JSON",
            data: {
                filter_order_status: "pending_orders",
                search_keyword: order_number,
                className : 'col-xl-12'
            },
            success: function(response) {
                if (response.status == 'Success') {
                    if (response.data.html != '') {
                        $("#received_new_orders").find(".modal-body").html('');
                        let latest_order_template = _.template($('#latest_order_template').html());
                        $("#received_new_orders").find(".modal-body").append(response.data.html);
                        if(response.data.auto_accept_status == 0){
                        	$("#received_new_orders").modal('show');
                        }
                    }
                }
            },
            error: function(data) {

            },
        });
    }
</script>
@if(@Session::has('preferences') && !empty(@Session::get('preferences')['fcm_api_key']))
<script>
    var firebaseCredentials = {!!json_encode(Session::get('preferences')) !!};
    //console.log(firebaseCredentials);
    var firebaseConfig = {
        apiKey: firebaseCredentials.fcm_api_key,
        authDomain: firebaseCredentials.fcm_auth_domain,
        projectId: firebaseCredentials.fcm_project_id,
        storageBucket: firebaseCredentials.fcm_storage_bucket,
        messagingSenderId: firebaseCredentials.fcm_messaging_sender_id,
        appId: firebaseCredentials.fcm_app_id,
        measurementId: firebaseCredentials.fcm_measurement_id
    };
    // Initialize Firebase
    firebase.initializeApp(firebaseConfig);

    const messaging = firebase.messaging();
    function initFirebaseMessagingRegistration() {

        messaging.requestPermission().then(function() {
            return messaging.getToken()
        }).then(function(token) {
            
            $.ajax({
                url: "{{ route('client.save_fcm') }}",
                type: "POST",
                data: {
                    "_token": "{{ csrf_token() }}",
                    fcm_token: token,
                },
                success: function(response) {

                },
            });
            console.log(token);
            console.log("token");

        }).catch(function(err) {
            console.log(`Token Error :: ${err}`);
        });
         @if(empty(Session::get('current_fcm_token')))
        @endif
    }

    initFirebaseMessagingRegistration();
    messaging.onMessage( async function(payload) {

        if (!("Notification" in window)) {
            console.log("This browser does not support system notifications.");
        }
        else if (Notification.permission === "granted") {
            console.log(payload);
            console.log("payload");
            if(payload && payload.data && payload.data.data){
                if(payload.data.type && payload.data.type=="order_created"){
                    var payload_data = JSON.parse(payload.data.data);
                    console.log('firepase msg order number');
                    console.log(payload_data.order_number);
                    setTimeout(()=>{
                         get_latest_order_socket(payload_data.order_number);
                    },3000);
                   
                }
                else if(payload.data.type=="order_cancellation_request"){
                    var notificationTitle = payload.notification.title;
                    var notificationOptions = {
                        body: payload.notification.body,
                        icon: payload.notification.icon
                    };
                    await fireNotification(notificationOptions,notificationTitle);
                    push_notification.onclick = function(event) {
                        event.preventDefault();
                        window.open(payload.notification.click_action, "_blank");
                        push_notification.close();
                    };
                }else if(payload.data.type=="reached_location"){
                    var payload_data = JSON.parse(payload.data.data);
                    get_reached_vendor_location_socket(payload_data.order_number,payload.data.type);
                } else {
                    //alert();
                    //setTimeout(()=>{
                        var notificationTitle = payload.notification.title;
                        var notificationOptions = {
                            body: payload.notification.body,
                            icon: payload.notification.icon
                        };
                        //console.log(notificationOptions);
                        await fireNotification(notificationOptions,notificationTitle);
                
                  
                     
                    //},2000);
                   
                }
            } 
        }
    });
    async function fireNotification(notificationOptions,notificationTitle){
        await new Notification(
            notificationTitle,
            notificationOptions
        );
    }
</script>
@endif
@endif
<script>
    @if(Auth::user())
    $(document).on("change",".admin_panel_theme", function(){
        if($(this).prop('checked')){
            var theme_admin = 'dark';
        }else{
            var theme_admin = 'light';
        }
        $.ajax({
            url: "{{route('configure.update', Auth::user()->code)}}",
            type: "POST",
            data: {
                theme_admin: theme_admin,
                "_token": "{{ csrf_token() }}",
            },
            success: function(response) {
                location.reload();
            },
        });

    });
    @endif
    @if(\Request::route()->getName() != 'order.index')
    $(document).on("click", ".update-status-ar", function() {
            let that = $(this);
            that.prop("disabled", true);
            var count = that.data("count");
            var full_div = that.data("full_div");
            var single_div = that.data("single_div");
            var status_option_id = that.data("status_option_id");
            var luxury_option = that.data("order_luxury_option");
            var status_option_id_next = status_option_id + 1;
            var order_vendor_id = that.data("order_vendor_id");
            var order_id = that.data("order_id");
            var vendor_id = that.data("vendor_id");
            var count = that.data("count");
            var order_luxury_option_id = that.data("order_luxury_option");
            var alertMessage = "";
            var productIds = [];
            var title = "";
            var totalCount = $(full_div + ' [data-count]').length;

            if (status_option_id == 9) {
                title = "{{__('Proceed with Accepting Order')}}";
            } else if (status_option_id == 10 ) {
                title = "{{__('Review Document')}}";
            } else if (status_option_id == 2 || status_option_id == 11) {
                title = "{{__('Accepting the Order')}}";
            } else if (status_option_id == 4 || status_option_id == 12) {
                title = "{{__('Schedule Meeting')}}";
            } else if (status_option_id == 5 || status_option_id == 13) {
                title = "{{__('Submit Document')}}";
            } else {
                title = "{{__('Complete the Session?')}}";
            }

            $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
                productIds[i] = $(this).val();
            });
            order_vendor_product_id = [];
            $('.productIdsCheck_' + order_id + ':checked').each(function(i) {
                order_vendor_product_id[i] = $(this).data('order_vendor_product_id');
            });
            if (status_option_id == 2 && that.data('is_alert')) {
                alertMessage = that.data('alert_message');
            }
            if (status_option_id == 3) {
                return openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id);
            } else if (status_option_id == 5 || status_option_id == 13) {
                // Show document upload modal
                $('#orderUploadId').val(order_id);
                $('#orderVendorId').val(order_vendor_id);
                $('#orderDocumentUploadModal').modal('show');
                that.prop("disabled", false);
                return;
            } else {
                if (productIds.length === 0 && order_luxury_option_id == 4 && status_option_id == 2) {
                    Swal.fire({
                        title: "{{__('Error')}}",
                        icon: 'warning',
                        text: "Please select at least one product",
                        showCancelButton: true,
                        confirmButtonText: 'Ok',
                    });
                    that.prop("disabled", false);
                } else {
                    Swal.fire({
                        title: title,
                        text: alertMessage,
                        showCancelButton: true,
                        confirmButtonText: 'Yes',
                    }).then((result) => {
                        if (result.value) {
                            $.ajax({
                                url: "{{ route('order.changeStatus') }}",
                                type: "POST",
                                data: {
                                    order_id: order_id,
                                    vendor_id: vendor_id,
                                    "_token": "{{ csrf_token() }}",
                                    status_option_id: status_option_id,
                                    order_vendor_id: order_vendor_id,
                                    productIds: productIds,
                                    order_vendor_product_id: order_vendor_product_id,
                                    order_luxury_option_id: order_luxury_option_id
                                },
                                success: function(response) {
                                    if ($('#received_new_orders').hasClass('show')) {
                                        $("#received_new_orders").modal('hide');
                                    }
                                    if (response.status == 'error') {
                                        if(status_option_id != 5 || status_option_id == 13){

                                            if (count == 0) {
                                                $(full_div).slideUp(1000, function() {
                                                    $(this).remove();
                                                });
                                            } else {
                                                $(single_div).slideUp(1000, function() {
                                                    $(this).remove();
                                                });
                                            }
                                        }
                                        that.prop("disabled", false);
                                        $.NotificationApp.send('{{__("Error")}}', response.message, "top-right", "#ff0808", "error");
                                        return 0;
                                    }
                                    location.reload();
                                    if (status_option_id == 4 || status_option_id == 5 || status_option_id == 10 || status_option_id == 2 ||status_option_id == 11 ||status_option_id == 12 || status_option_id == 13) {
                                        if (status_option_id == 4 || status_option_id == 12) {
                                            if ((luxury_option == 2) || (luxury_option == 3)) {
                                                var next_status = "{{ __('Order Prepared') }}";
                                            } else {
                                                var next_status = "{{ __('Submit Document') }}";
                                            }
                                        } else if (status_option_id == 10) {
                                            var next_status = "{{ __('Approve Request') }}";
                                        } else if (status_option_id == 2 ||status_option_id == 11) {
                                            var next_status = "{{ __('Schedule Meeting') }}";
                                        } else if (status_option_id == 5 || status_option_id == 13) {
                                            // var next_status = "{{ __('Session Completed') }}";
                                        }
                                        that.prop("disabled", false);
                                        that.replaceWith("<button class='update-status-ar btn-warning' data-full_div='" + full_div + "' data-single_div='" + single_div + "'  data-count='" + count + "'  data-order_id='" + order_id + "'  data-vendor_id='" + vendor_id + "'  data-status_option_id='" + status_option_id_next + "' data-order_vendor_id=" + order_vendor_id + ">" + next_status + "</button>");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                        return false;
                                    } else {
                                    
                                        if (status_option_id == 9 ||status_option_id == 6 ||status_option_id == 14) {
                                            if (count == 0) {
                                                if (totalCount > 2) {
                                                    $(single_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                } else {
                                                    $(full_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                }
                                            } else {
                                                if (totalCount > 2) {
                                                    $(single_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                } else {
                                                    $(full_div).slideUp(1000, function() {
                                                        $(this).remove();
                                                    });
                                                }
                                            }
                                        }
                                        that.prop("disabled", false);
                                    }
                                    if (status_option_id == 2) {
                                        that.prop("disabled", false);
                                        getOrderCount("pending-orders", "active-orders");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    } else {
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    }
                                    if (status_option_id == 6) {
                                        that.prop("disabled", false);
                                        getOrderCount("active-orders", "history-orders");
                                        $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                                    }
                                },
                                error: function(xhr, status, error) {
                                    console.error("AJAX Error: ", status, error);
                                    that.prop("disabled", false);
                                    $.NotificationApp.send('{{__("Error")}}', "An error occurred while processing your request.", "top-right", "#ff0808", "error");
                                }
                            });
                        } else {
                            that.prop("disabled", false);
                        }
                    });
                }
            }
        });
    @endif
    function openRejectModal(order_id, vendor_id, status_option_id, order_vendor_id, order_luxury_option_id) {
        var cancelled_by = "{{Auth::user()->id}}";
        $('#addRejectmodal').modal({
            backdrop: 'static',
            keyboard: false,

            });
            $('.addrejectSubmit').on('click', function(e) {
                e.preventDefault();
                var reject_reason = $('#addRejectForm #AddRejectBox .reject_reason').val();

                // var that = document.getElementById('reject');
                var count = $("#reject").data("count");
                var full_div = $("#reject").data("full_div");
                var single_div =$("#reject").data("single_div");
                $.ajax({
                    url: "{{ route('order.changeStatus') }}",
                    type: "POST",
                    data: {
                        order_id: order_id,
                        vendor_id: vendor_id,
                        reject_reason: reject_reason,
                        "_token": "{{ csrf_token() }}",
                        status_option_id: status_option_id,
                        order_vendor_id: order_vendor_id,
                        cancelled_by: cancelled_by,
                        order_luxury_option_id: order_luxury_option_id,
                    },

                    success: function(response) {
                        if (response.status == 'success') {
                            $(".modal .close").click();
                            $.NotificationApp.send('{{__("Success")}}', response.message, "top-right", "#5ba035", "success");
                            setTimeout(function() {
                                window.history.back();
                            }, 3000);
                        } else if (response.status == 'error') {
                            $('#error-case').empty();
                            $('#error-case').append(response.message);
                        }
                        if (count == 0) {
                            $(full_div).slideUp(2000, function() {
                                $(this).remove();
                            });
                            if (response.status == 'success') {
                                setTimeout(function() {
                                    window.history.back();
                                }, 3000);
                            }
                        } else {
                            $(single_div).slideUp(2000, function() {
                                $(this).remove();
                            });
                            if (response.status == 'success') {
                                setTimeout(function() {
                                    window.history.back();
                                }, 3000);
                            }
                        }
                    },
                    error: function(response) {
                        if (response.status == 'error') {
                            $('#error-case').empty();
                            $('#error-case').append(response.message);
                        }
                    }

            });


        });


    }
    
</script>
@endif


@yield('script-bottom')
{{-- <script  src="{{asset('assets/js/chat/chatNotifications.js')}}"></script> --}}
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-5LPF1QP3Y3"></script>
<script>
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());

gtag('config', 'G-5LPF1QP3Y3');
@if(isset($analytics['gtag_id']))
    gtag('config', "{{$analytics['gtag_id'] ?? ''}}");
@endif   

$("#change_password").on("hidden.bs.modal", function(){
    $('.pwd-msg').html("");
    $('#change_password_form').trigger("reset");
});


$("#change_password_form").submit(function(e){
   // return false;
    e.preventDefault();
    $('.pwd-msg').html("");
    $.ajax({
            url:"{{route('cl.password.update')}}",
            type:'POST',
            data:$(this).serialize(),
            dataType:'JSON',
            success:function(result){
                if(result.type=="error")
                {
                    var pwderror = '<span class="text-danger" role="alert"><strong>'+result.message+'</strong></span>';
                    $('.pwd-msg').html(pwderror);                    
                }else{
                    var pwderror = '<span class="text-success" role="alert"><strong>'+result.message+'</strong></span>';
                    $('.pwd-msg').html(pwderror);
                    $('#change_password_form').trigger("reset");
                    setTimeout(function () {                        
                        $('#change_password').modal('toggle');
                    }, 1000);
                }
            }

    });
});
</script> 
@if(isset($analytics['fpixel_id']))
    <!-- Meta Pixel Code -->
        <script>
        !function(f,b,e,v,n,t,s)
        {if(f.fbq)return;n=f.fbq=function(){n.callMethod?
        n.callMethod.apply(n,arguments):n.queue.push(arguments)};
        if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
        n.queue=[];t=b.createElement(e);t.async=!0;
        t.src=v;s=b.getElementsByTagName(e)[0];
        s.parentNode.insertBefore(t,s)}(window, document,'script',
        'https://connect.facebook.net/en_US/fbevents.js');
        fbq('init', "{{$analytics['fpixel_id']}}");
        fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{$analytics['fpixel_id']}}&ev=PageView&noscript=1"/></noscript>
    <!-- End Meta Pixel Code -->
@endif
<script>
    $(".menu_cta").click(function(){
        $("html").toggleClass("active_slidebar");
    });
</script> 