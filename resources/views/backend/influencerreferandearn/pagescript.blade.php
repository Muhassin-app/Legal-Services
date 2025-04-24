<script>
$(".addAttributbtn").click(function(e) {
    console.log('click function called');
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    e.preventDefault();
    var did = $(this).attr('dataid');
    $.ajax({
        type: "get",
        url: "{{route('attribute-influencer-refer-earn.create')}}",
        data: '',
        dataType: 'json',
        success: function(data) {
            $('#addAttributemodal').modal({
                backdrop: 'static',
                keyboard: false
            });
            $('#addAttributeForm #AddAttributeBox').html(data.html);
            $('.dropify').dropify();
            $('.selectize-select').selectize();

            var picker = new jscolor('#add-hexa-colorpicker-1', options);
        },
        error: function(data) {
            console.log('data2');
        }
    });

});

// Edit Influencer Attribute
$('.editAttributeBtn').on('click', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    e.preventDefault();
    var did = $(this).attr('dataid');
    $.ajax({
        type: "get",
        url: "{{url('client/influencer-refer-earn/attribute/edit')}}" + '/' + did,
        data: '',
        dataType: 'json',
        beforeSend: function() {
            $(".loader_box").show();
        },
        success: function(data) {
            $('#editAttributemodal').modal({
                backdrop: 'static',
                keyboard: false
            });
            
            $('#editAttributeForm #editAttributeBox').html(data.html);
            $('.dropify').dropify();
            $('.selectize-select').selectize();
            $("#editAttributeForm .hexa-colorpicker").each(function() {
                var ids = $(this).attr('id');
                try {
                    var picker = new jscolor('#' + ids, options);
                } catch (err) {
                    console.log(err.message);
                }
            });
            var getURI = document.getElementById('submitEditHidden').value;
            document.getElementById('editAttributeForm').action = data.submitUrl;
        },
        error: function(data) {
            console.log('data2');
        },
        complete: function() {
            $('.loader_box').hide();
        }
    });
});

// Delete
$(document).on('click', '.deleteAttribute', function() {
    var did = $(this).attr('dataid');
    Swal.fire({
        title: "{{__('Are you sure?')}}",
        text:"{{__('You want to delete this attribute.')}}",
        icon: 'info',
        showCancelButton: true,
        confirmButtonText: 'Ok',
    }).then((result) => {
        if(result.value)
        {
            $('#attrDeleteForm' + did).submit();
        }
    });
    return false;
});

// Add another option
$(document).on('click', '.addOptionRow-attribute-edit', function(e) {
    var d = new Date();
    var n = d.getTime();
    var $tr = $('.optionTableEditAttribute tbody>tr:first').next('tr');
    var $clone = $tr.clone();
    $clone.find(':text').val('');
    $clone.find(':hidden').val('');
    $clone.find('.hexa-colorpicker').attr("id", "hexa-colorpicker-" + n);
    $clone.find('.lasttd').html('<a href="javascript:void(0);" class="action-icon deleteCurRow"> <i class="mdi mdi-delete"></i></a>');
    $('.optionTableEditAttribute').append($clone);
    var picker = new jscolor("#hexa-colorpicker-" + n, options);
});

// Remove row when add new attribute
$("#addAttributemodal").on('click', '.deleteCurRow', function() {
    $(this).closest('tr').remove();
});

// Remove row when edit attribute
$("#editAttributemodal").on('click', '.deleteCurRow', function() {
    var delete_attr_id = $(this).data('delete_attr_id');
    var closet_tr = $(this).closest('tr');

    if( delete_attr_id != 'undefined' && delete_attr_id != undefined ) {
        $.ajax({
            type: "POST",
            url : "{{route('deleteAttribute')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                "id": delete_attr_id
            },
            beforeSend: function() {
                $(".editAttributeSubmit").attr("disabled", true);
            },
            success: function (response) {
                
                if(response.success) {
                    closet_tr.remove();
                } else {
                    $('.delete_options').removeClass('d-none');
                }
            },
            error: function(error) {
                $('.delete_options').removeClass('d-none');
            },
            complete: function() {
                $(".editAttributeSubmit").attr("disabled", false);
            }
        });
    }
});
</script>