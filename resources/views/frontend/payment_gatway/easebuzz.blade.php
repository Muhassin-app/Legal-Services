<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!------ Include the above in your HEAD tag ---------->
<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.1.0/css/all.css" integrity="sha384-lKuwvrZot6UHsBSfcMvOkWwlCMgc0TaWr+30HWe3a4ltaBwTZhyTEggF5tJv8tbt" crossorigin="anonymous">
<div class="container">

    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7 pb-5" style="margin-top: 2%;">
            <!--Form with header-->
            <form role="form" action="{{route('easebuzz.order')}}" method="post">
                {!! csrf_field() !!}
                <div class="card border-primary rounded-0">
                    <div class="card-header p-0">
                        <div class="bg-info text-white text-center py-2">
                            <h3>Easebuzz Payment Gateway Integration </h3>
                        </div>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-sm-12">

                                @if(Session::has('successMessage'))
                                    <div class="alert alert-success">
                                        <a class="close" data-dismiss="alert">×</a>
                                        <strong>Alert </strong> {!!Session::get('successMessage')!!}
                                    </div>
                                @endif

                                @if(Session::has('errorMessage'))
                                    <div class="alert alert-success">
                                        <a class="close" data-dismiss="alert">×</a>
                                        <strong>Alert </strong> {!!Session::get('errorMessage')!!}
                                    </div>
                                @endif


                                <div class="form-group">
                                    <label for="name">Customer Name</label>
                                    <input type="text" class="form-control" name="customerName" placeholder="Customer Name"  value="{{ old('customerName') }}">
                                    @if ($errors->has('customerName'))
                                        <span style="color: red;">{{ $errors->first('customerName') }}</span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="name">Customer Phone</label>
                                    <input type="text" class="form-control" name="customerPhone" placeholder="Customer Name"  value="{{ old('customerPhone') }}">
                                    @if ($errors->has('customerPhone'))
                                        <span style="color: red;">{{ $errors->first('customerPhone') }}</span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="name">Customer Email</label>
                                    <input type="text" class="form-control" name="customerEmail" placeholder="Customer Name"  value="{{ old('customerEmail') }}">
                                    @if ($errors->has('customerEmail'))
                                        <span style="color: red;">{{ $errors->first('customerEmail') }}</span>
                                    @endif
                                </div>

                                <div class="form-group">
                                    <label for="name">Amount</label>
                                    <input type="text" class="form-control" name="amount" placeholder="Amount"  value="{{ old('amount') }}">
                                    @if ($errors->has('amount'))
                                        <span style="color: red;">{{ $errors->first('amount') }}</span>
                                    @endif
                                </div>

                            </div>
                        </div>

                        <div class="text-center">
                            <button type="submit" class="btn btn-info btn-block rounded-0 py-2">Submit</button>
                        </div>
                    </div>

                </div>
            </form>
            <!--Form with header-->
        </div>
    </div>
</div>