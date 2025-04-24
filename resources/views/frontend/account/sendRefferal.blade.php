@extends('layouts.store', ['title' => 'Login'])

@section('css')
<style type="text/css">
    .main-menu .brand-logo {
        display: inline-block;
        padding-top: 20px;
        padding-bottom: 20px;
    }
</style>

@endsection

@section('content')

<style type="text/css">
    .productVariants .firstChild {
        min-width: 150px;
        text-align: left !important;
        border-radius: 0% !important;
        margin-right: 10px;
        cursor: default;
        border: none !important;
    }

    .product-right .color-variant li,
    .productVariants .otherChild {
        height: 35px;
        width: 35px;
        border-radius: 50%;
        margin-right: 10px;
        cursor: pointer;
        border: 1px solid #f7f7f7;
        text-align: center;
    }

    .productVariants .otherSize {
        height: auto !important;
        width: auto !important;
        border: none !important;
        border-radius: 0%;
    }

    .product-right .size-box ul li.active {
        background-color: inherit;
    }

    .login-page .theme-card .theme-form input {
        margin-bottom: 5px;
    }

    .invalid-feedback {
        display: block;
    }
</style>

<section class="login-page section-b-space">
    <div class="container">
        <div class="row my-md-3 mt-5 pt-4">
            <div class="offset-lg-3 col-lg-6">
                <div class="dashboard-right">
                    <h3>Send Refferal</h3>
                    <div class="dashboard">
                        <div class="card-box">
                            <div class="welcome-msg">
                                <h5>Hello, {{ucwords(Auth::user()->name)}} !</h5>
                                <!-- <p>To enjoy shopping from our website, Please verify below information. So you will not face any interruption in future.</p> -->
                            </div>
                            <div id="msg"></div>

                            <div class="box-account box-info">
                                <!-- <form name="register" id="register" action="{{route('customer.register')}}" class="theme-form" method="post"> @csrf -->
                                <form id="sendEmail" class="theme-form">@csrf
                                    <div class="form-row mb-3">
                                        <div class="col-md-12">
                                            <label for="email">Enter email address</label>
                                            <input type="text" class="form-control" id="email" placeholder="Email" name="email" required="" value="{{ old('email')}}">
                                            @if($errors->first('email'))
                                            <span class="invalid-feedback" role="alert">
                                                <strong>{{ $errors->first('email') }}</strong>
                                            </span>
                                            @endif
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn btn-solid mt-3">Send Email</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


@endsection

@section('script')

<script type="text/javascript">


    $("#sendEmail").submit(function(event) {
        event.preventDefault();
        //console.log("fregwr");
        var form = document.getElementById('sendEmail');
        var formData = new FormData(form);
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            type: "post",
            dataType: "json",
            url: "{{ route('user.sendEmail') }}",
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                // Swal.fire({
                //    title: "Success!",
                //    text: "Sent Successfully.",
                //    icon: "success",
                //    button: "OK",
                // });
                var html ='<div class="alert alert-success"><span>{{__("Sent Successfully")}}</span></div>';
                $('#msg').html(html);
                setTimeout(reload, 3000);
               // alert("Sent Successfully");
               // window.location.href = "{{route('user.profile')}}";
            },
            error: function(data) {
                $(".invalid-feedback2").html(data.responseJSON.error);
                console.log(data.responseJSON.error);
            },
        });
    });
    function reload(){
        window.location.href = "{{route('user.profile')}}";
    }
</script>

@endsection
