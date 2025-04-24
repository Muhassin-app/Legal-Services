<div id="razorpay-connect-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">Razorpay Connect Bank Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
                <div class="modal-body px-3 py-2">
                    <div class="row">
                     
                        <div class="col-md-6">
                            <div class="form-group">
                               <button class="btn btn-primary" id="razorpay_connect" type="button" >Create Contact</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-1">
                              <b class="text-success">{{((@$vendor->vendor_contact_json)?'Contact Created':'')}}</b>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <button type="button" class="btn btn-primary" id="razorpay_bank_modal"  >Connect with Bank</button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group mt-1">
                              <b class="text-success">{{((@$vendor->vendor_bank_json->id)?'Bank Connected':'')}}</b>
                            </div>
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>

<div id="razorpay-add-bank-modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" style="display: none" aria-modal="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h4 class="modal-title">Razorpay Connect Account Funds Details</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
                <div class="modal-body px-3 py-2">
                        <form method="POST" action="{{route('vendor.add.fund.account')}}" >
                            <input type="hidden" name="vid" value="{{$vendor->id}}">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="field-1" class="control-label">Name</label>
                                        <input name="name" type="text" value="{{@$vendor->vendor_bank_json->bank_account->name}}" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="field-1" class="control-label">IFSC</label>
                                        <input name="ifsc" type="text" value="{{@$vendor->vendor_bank_json->bank_account->ifsc}}" class="form-control" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="field-2" class="control-label">Account Number</label>
                                        <input type="password" name="acc_no" class="form-control" value="" >
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="field-2" class="control-label">Re-Enter Account Number</label>
                                        <input type="password" name="re_acc_no" class="form-control" value="" >
                                    </div>
                                </div>
                            </div>
                            <button class="btn btn-primary " >Submit</button>
                        </form>
                </div>
        </div>
    </div>
</div>
<script>

$('#razorpay_connect').click(function() {
        $.ajax({
            type: "post",
            url: "{{route('vendor.razorpay_connect')}}",
            data: {'vid':"{{$vendor->id}}"},
            success: function(response) {
                if(response.status == '200'){
                    console.log(response);
                    window.location.reload();
                }
            },
            error:function(error){
                console.log(error);
                window.location.reload();
            }
        });
        $('#razorpay-connect-modal').modal('hide');
    });

$('#razorpay_bank_modal').click(function() {
    $('#razorpay-connect-modal').modal('hide');
    $('#razorpay-add-bank-modal').modal();
});
    
</script>