$(document).on("change", ".pickup_schedule_datetime_re", function() {
    var schedule_dt = $(this).val();
    var vendor_id = $('#vendor_id').val();
    var parentRowID = this.parentNode.parentNode.id;
    var schedule_pickup_slot = '#'+document.getElementById(parentRowID).childNodes[3].childNodes[1].id;
    $('#loaderforjs').show();    
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: check_pickup_schedule_slots,
        data: { date: schedule_dt,vendor_id:vendor_id},
        success: function(response) {
            if (response.status == "Success") {
                $(schedule_pickup_slot).html(response.data);
                $('#loaderforjs').hide();  
            }else{
                success_error_alert('error', response.message, ".cart_response");
               $(schedule_pickup_slot).html(response.data);
               $('#loaderforjs').hide();  
            }
        },
        error: function(error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            $('#loaderforjs').hide();  
        }
    });
});

$(document).on("change", ".dropoff_schedule_datetime_re", function() {
    var schedule_dt = $(this).val();
    var vendor_id = $('#vendor_id').val();
    var parentRowID = this.parentNode.parentNode.id;
    var schedule_dropoff_slot = '#'+document.getElementById(parentRowID).childNodes[3].childNodes[1].id;
    $('#loaderfordrop').show();    
    $.ajax({
        type: "POST",
        dataType: 'json',
        url: check_dropoff_schedule_slots,
        data: { date: schedule_dt,vendor_id:vendor_id},
        success: function(response) {
            if (response.status == "Success") {
                $(schedule_dropoff_slot).html(response.data);
                $('#loaderfordrop').hide();  
            }else{
                success_error_alert('error', response.message, ".cart_response");
               $(schedule_dropoff_slot).html(response.data);
               $('#loaderfordrop').hide();  
            }
        },
        error: function(error) {
            var response = $.parseJSON(error.responseText);
            success_error_alert('error', response.message, ".cart_response");
            $("#order_placed_btn, .proceed_to_pay").removeAttr("disabled");
            $('#loaderfordrop').hide();  
        }
    });
});


$('.rescheduleOrder').each( function(){
    var form = $(this);
    var pickup_schedule_datetime = form.find("input.pickup_schedule_datetime_re");
    form.validate({
        rules:
        {
            pickup_schedule_datetime: 
            { 
                required:true,
            },
           schedule_pickup_slot: 
            { 
                required:true,
            },
            dropoff_schedule_datetime: 
            { 
                required:true,
                notEqualTo: pickup_schedule_datetime,
                greaterThan: pickup_schedule_datetime,
            },
           schedule_dropoff_slot: 
            { 
                required:true,
            },
        },
    messages:
        {
            pickup_schedule_datetime:
            {
             required: "Please select the pickup date.",
            }, 
            schedule_pickup_slot:
            {
              required: "Please select the pickup slot.",
            },
            dropoff_schedule_datetime:
            {
             required: "Please select the dropoff date.",
             notEqualTo: "Pickup and dropoff date cannot be same.",
             greaterThan: "Dropoff date cannot be less than pickup date.",
            }, 
            schedule_dropoff_slot:
            {
              required: "Please select the dropoff slot.",
            },               
         },
    });
});
// $(".rescheduleOrder").validate({
//     rules:
//         {
//             pickup_schedule_datetime: 
//             { 
//                 required:true,
//             },
//            schedule_pickup_slot: 
//             { 
//                 required:true,
//             },
//             dropoff_schedule_datetime: 
//             { 
//                 required:true,
//                 notEqualTo: "#pickup_schedule_datetime",
//                 greaterThan: "#pickup_schedule_datetime",
//             },
//            schedule_dropoff_slot: 
//             { 
//                 required:true,
//             },
//         },
//     messages:
//         {
//             pickup_schedule_datetime:
//             {
//              required: "Please select the pickup date.",
//             }, 
//             schedule_pickup_slot:
//             {
//               required: "Please select the pickup slot.",
//             },
//             dropoff_schedule_datetime:
//             {
//              required: "Please select the dropoff date.",
//              notEqualTo: "Pickup and dropoff date cannot be same.",
//              greaterThan: "Dropoff date cannot be less than pickup date.",
//             }, 
//             schedule_dropoff_slot:
//             {
//               required: "Please select the dropoff slot.",
//             },               
//          },
// });