<script>
    $(document).delegate(".add-role", "click", function(){
        $('.role-name').val('');
        $('.role-id').val('');
        $("#add-role-modal").modal("show");
    });

    $(document).on('click', '.edit-role', function(e) {
        var id = $(this).attr('data-id');
        callAjax(id)
    });

    $(document).ready(function(){
        var role_id = $('li.role_name.active').data('id');
        getRolePermission(role_id);
    });

    $(document).on('click', 'li.role_name', function() {
        const role_id = $(this).data('id');
        const $this = $(this);
        $('li.role_name').removeClass('active');
        $this.addClass('active');
        getRolePermission(role_id);
    });

    function getRolePermission(role_id) {
        spinnerJS.showSpinner();
        const url = "{{ route('get.role.permission') }}";
        const data = { role_id: role_id };
    
        $.post(url, data, function(response) {
            spinnerJS.hideSpinner();
            $('#permission-data').html(response.data.permission_html);
        }, 'json');
    }

    $(document).on('click', '.access_module', function(){
        var role_id = $('li.role_name.active').data('id');
        var checkedPermission = $('.access_module:checked').map(function() {
            return $(this).val();
        }).get();
        const url = "{{ route('save.role.permissions') }}";
        const data = { role_id: role_id, checkedPermission:checkedPermission };
        $.post(url, data, function(response) {
            console.log(response.status);
            if(response.status == 'success'){
                $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
            }else{
                $.NotificationApp.send("Error", response.message, "top-right", "#ab0535", "error");
            }
        }, 'json');
    });
    
    function callAjax(id){
        var id = id;
        $.ajax({
            method: "post",
            headers: {
                Accept: "application/json",
            },
            url: "{{route('get.role') }}",
            data: 'id='+id,
            success: function (response) {
                if (response) {
                    $("#add-role-modal").modal("show");
                    $('.role-name').val(response.role.name);
                    $('.role-id').val(response.role.id);
                    $('.select2').show();
                    $('.selectTo').html(response.select);
                    $('.permissoin-multiple').select2();
                }else{
                    alert('Try Again!');
                }
            }
        });
    }
</script>