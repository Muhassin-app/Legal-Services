
(function() {
    datepic();
    GiftCardDataTable();
    var datatable = '';
 })();
function datepic(){
    $('.datetime-datepicker').flatpickr({
        enableTime: true,
        startDate: new Date(),
        minDate: new Date(),
        dateFormat: "Y-m-d H:i"
    });
}
  
$(document).on('click', '.submitGiftCardForm', function(e) {
    e.preventDefault();
    $('.submitGiftCardForm').attr("disabled", true);
    var form = document.getElementById('giftCardForm');
    var formData = new FormData(form); 
    var urls = "/client/gitcart/store";
    saveGiftCardData(formData, 'add', urls);
});

function saveGiftCardData(formData, type, url) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        type: "post",
        headers: {
            Accept: "application/json"
        },
        url: url,
        data: formData,
        contentType: false,
        processData: false,
        success: function(response) {
            if (response.status == 'success') {
                $('.dropify').dropify().data('dropify').resetPreview();
                $('.submitGiftCardForm').attr("disabled", false);
                $('span.invalid-feedback').hide();
                document.getElementById('giftCardForm').reset();
                $('#agiftCart_model').modal('hide');
                $('#EditagiftCart_model').modal('hide');
                 Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: response.message,
                });
                setTimeout(() => {
                    
                    $('#add-service').modal('hide');
                    $(".submitGiftCardForm").removeAttr("disabled");
                    datatable.ajax.reload();
                },1000);
              
            } else {
                $(".show_all_error.invalid-feedback").show();
                $(".show_all_error.invalid-feedback").text(response.message);
            }
            return response;
        },
        error: function(response) {
            $(".submitGiftCardForm").removeAttr("disabled");
            if (response.status === 422) {
                let errors = response.responseJSON.errors;
                Object.keys(errors).forEach(function(key) {
                    $("#" + key + "Input input").addClass("is-invalid");
                    $("#" + key + "Input span.invalid-feedback").children("strong").text(errors[key][0]);
                    $("#" + key + "Input span.invalid-feedback").show();
                });
            } else {
                $(".show_all_error.invalid-feedback").show();
                $(".show_all_error.invalid-feedback").text('Something went wrong, Please try Again.');
            }
            return response;
        }
    });
}

function GiftCardDataTable(){
    //$("#giftCard_datatable").dataTable().fnDestroy()
    datatable = $('#giftCard_datatable').DataTable({
        processing: true,
        responsive: true,
        searching: false,
        scrollY: '200px',
        responsive: true,
        destroy: true,
        scrollCollapse: true,
        lengthChange: false,
        ajax: `/client/gitcart`,
        columns: [
            { data: 'DT_RowIndex' },
            {
                data: 'image_url',
                name: 'image_url',
                orderable: false,
                searchable: false,
                "mRender": function(data, type, full) {
                    return "<img src='" + full.image_url + "' class='rounded-circle' alt='" + full.id + "' >";
                }
            },
            { data: 'name',
            orderable: false },
            { data: 'amount',
            orderable: false },
            { data: 'expiry_date',
            orderable: false, },
            { data: 'action',
            orderable: false, },
        
        ]
     
    });
}
$(document).on('click', '.editGiftCard', function(e) {
    e.preventDefault();
    var giftcardId =$(this).attr('data-gify-card');
    getGeftCart(giftcardId)
  console.log(giftcardId);
});
$(document).on('click', '.submitEditGiftCardForm', function(e) {
    e.preventDefault();
    var giftId  = $('#editGiftCard').val();
    var form = document.getElementById('editgiftCardForm');
    var formData = new FormData(form); 
    var urls = `/client/gitcart/update/${giftId}`;
    saveGiftCardData(formData, 'add', urls);
});
function getGeftCart(id){
    $.ajax({
        type: "get",
        url: `/client/gitcart/show/${id}`,
        dataType: 'json',
        success: function(response) {
           $('#EditagiftCart_model #editgiftCard').html(response.html);
           $('#EditagiftCart_model').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('.dropify').dropify();
            datepic();
        },
        error: function(data) {
            console.log('data2');
        }
    });
}

$(document).on('click', '.deleteGiftCard', function(e) {
    e.preventDefault();
    var giftcardId =$(this).attr('data-gify-card');
 
    Swal.fire({
        title: 'Warning!',
        text: 'Are you sure?',
        icon: 'warning',
      }).then(({value}) => {
        console.log(value);
            if (value === true) {
                deleteGiftCard(giftcardId);
            } 
      });

});
function deleteGiftCard(id){
    axios.get(`/client/gitcart/delete/${id}`)
    .then(async response => {
   
        if(response.data.success){
            sweetAlert.success('Success',response.data.message);
        } else{
            sweetAlert.error('',response.data.message);
        }
        setTimeout(() => {
            datatable.ajax.reload();
        },1000);
    })
    .catch(e => {
        sweetAlert.error();
    })    
}