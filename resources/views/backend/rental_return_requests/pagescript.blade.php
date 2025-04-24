<script>
    $(document).ready(function() {
        var table;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        setTimeout(function(){$('#approved-requests').trigger('click');}, 200);
        
        $(document).on("click",".nav-link",function() {
            let rel= $(this).data('rel');
            let status= $(this).data('status');
            initDataTable(rel, status);
        });
        $("#vendor_select_box").change(function() {
            intialize();
        });
        $("#range-datepicker").flatpickr({
            mode: "range",
            onClose: function(selectedDates, dateStr, instance) {
               intialize();
            }
        });
        $("#clear_filter_btn_icon").click(function() {
            $('#range-datepicker').val('');
            $('#vendor_select_box').val('');
            init("pending_orders", "{{ route('cancel-order.requests.filter') }}", '', false);
        });
        $(document).on("input", "#search_via_keyword", function(e) {
            intialize();
        })
        
        function initDataTable(table, status) {
            var dynamic_columns = [
                {data: 'order_number', name: 'order_number', orderable: false, searchable: true,"mRender": function ( data, type, full ) {
                    return "<a href='"+full.order_detail_url+"'>"+data+"</a>";
                }},
                {data: 'vendor', name: 'vendor', orderable: false, searchable: true, "mRender": function ( data, type, full ) {
                    return "<a href='"+full.show_vendor_url+"'>"+data+"</a>";
                }},
                {data: 'reject_reason', name: 'reject_reason', orderable: false, searchable: true},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {data: 'requested_date', name: 'requested_date', orderable: false, searchable: false},
            ];
            if(status == 0){
                dynamic_columns.push({data: 'action', class:'text-center', name: 'action', orderable: false, searchable: false});
            }else{
                dynamic_columns.push({data: 'updated_date', name: 'updated_date', orderable: false, searchable: false});
                dynamic_columns.push({data: 'updated_by', name: 'updated_by', orderable: false, searchable: false});
            }

            $('#'+table).DataTable({
                "destroy": true,
                "scrollX": true,
                "processing": true,
                "serverSide": true,
                "iDisplayLength": 20,
                "dom": '<"toolbar">Bftrip',
                language: {
                    search: "",
                    info: table_info,
                    paginate: { previous: "<i class='mdi mdi-chevron-left'>", next: "<i class='mdi mdi-chevron-right'>" },
                    searchPlaceholder: search_text
                },
                drawCallback: function () {
                    $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                },
                buttons: [],
                ajax: {
                  url: base_url+'/client/cancel-order/requests/filter',
                  complete: function(){
                    $('.vendor-products').removeClass('invisible');
                  },
                  data: function (d) {
                    d.status = status;
                    d.search = $('.dataTables_filter input[type="search"]').val();
                    d.date_filter = $('#range-datepicker').val();
                    d.vendor_id = $('#vendor_select_box option:selected').val();
                    d.search_keyword = $('#search_via_keyword').val();
                  }
                },
                columns: dynamic_columns
            });
        }

        $(document).on('click', '.complete_request_btn', function(e) {
            let id = $(this).attr('data-id');
            let status = $(this).attr('data-status');
            let title = $(this).attr('title');
            Swal.fire({
                title: "Are you sure?",
                text: "You really want to "+ title +" this request?",
                icon: 'warning',
                iconColor: '{{getClientPreferenceDetail()->web_color}}',
                showCancelButton: true,
                confirmButtonText: 'Yes, '+ title + ' it!',
                confirmButtonColor: '{{getClientPreferenceDetail()->web_color}}'
            }).then((result) => {
                if(result.value)
                {
                    if(title != "Approve"){
                        $('#vendor_order_reject').modal('show');
                        $('#vendor_order_reject #id').val(id);
                        $('#vendor_order_reject #status').val(status);
                        $('#vendor_order_reject #title').val(title);
                    }
                    if(title == "Approve"){
                        $.ajax({
                            type: "POST",
                            data: {id: id, status: status},
                            url: cancel_request_update_url,
                            headers: {Accept: "application/json"},
                            success: function(response) {
                                if (response.status == 'Success') {
                                    $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                                    setTimeout(function(){location.reload();}, 2500);
                                } else {
                                    Swal.fire({
                                        text: response.message,
                                        icon : "error",
                                        button: "OK",
                                    });
                                    return false;
                                }
                            },
                            beforeSend: function(){
                                $(".loader_box").show();
                            },
                            complete: function(){
                                $(".loader_box").hide();
                            },
                            error: function(response) {
                                let error = response.responseJSON;
                                Swal.fire({
                                    text: error.message,
                                    icon : "error",
                                    button: "OK",
                                });
                                return false;
                            }
                        });
                    }
                }
            });
        });

        $(document).on('click', '.vendorrejectReqSubmit', function(e) {
            let id = $("#vendor_order_reject #id").val();
            let status = $("#vendor_order_reject #status").val();
            let title = $("#vendor_order_reject #title").val();
            let vendor_reject_reason = $("#vendor_order_reject #reject_reason").val();
            $.ajax({
                type: "POST",
                data: {id: id, status: status, vendor_reject_reason: vendor_reject_reason},
                url: cancel_request_update_url,
                headers: {Accept: "application/json"},
                success: function(response) {
                    if (response.status == 'Success') {
                        $('#vendor_order_reject').modal('hide');
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                        setTimeout(function(){location.reload();}, 2500);
                    } else {
                        Swal.fire({
                            text: response.message,
                            icon : "error",
                            button: "OK",
                        });
                        return false;
                    }
                },
                beforeSend: function(){
                    $(".loader_box").show();
                },
                complete: function(){
                    $(".loader_box").hide();
                },
                error: function(response) {
                    let error = response.responseJSON;
                    Swal.fire({
                        text: error.message,
                        icon : "error",
                        button: "OK",
                    });
                    return false;
                }
            });
        });

    });

</script>
