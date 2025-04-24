@extends('layouts.god-vertical', ['title' => 'Lumen'])
@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Lumen Entries</h4>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-8">
                            <div class="text-sm-left">
                                @if (\Session::has('success'))
                                <div class="alert alert-success">
                                    <span>{!! \Session::get('success') !!}</span>
                                </div>
                                @endif
                                @if (\Session::has('erro'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error') !!}</span>
                                </div>
                                @endif
                                @if (\Session::has('error_delete'))
                                <div class="alert alert-danger">
                                    <span>{!! \Session::get('error_delete') !!}</span>
                                </div>
                                @endif
                            </div>
                        </div>
                        <div class="col-sm-4 text-right">
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#lumen-modal">Add</button>
                            {{-- <a class="btn btn-info waves-effect waves-light text-sm-right"
                                href="javascript:void(0);"><i class="mdi mdi-plus-circle mr-1"></i> Add
                            </a> --}}
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-centered table-nowrap table-striped" id="products-datatable">
                            <thead>
                                <tr>
                                    <th>Domain</th>
                                    <th>Short Code</th>
                                    <th>Database Name</th>
                                    <th>API Key</th>
                                </tr>
                            </thead>
                            <tbody>

                                @if($clients)
                                @foreach ($clients as $client)
                                <tr>
                                    <td>{{ $client->domain ?? 'N/A'}} </td>
                                    <td>{{ $client->code ?? 'N/A'}} </td>
                                    <td>{{ $client->database ?? 'N/A'}} </td>
                                    <td>{{ $client->lumen_access_token ?? 'N/A'}} </td>
                                </tr> 
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="pagination pagination-rounded justify-content-end mb-0">
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


@endsection
<script>



    function generateRandomString(length) {
        var text = "";
        var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

        for (var i = 0; i < length; i++)
            text += possible.charAt(Math.floor(Math.random() * possible.length));

        return text;
    }
    function genrateKeyAndToken() {
        var key = generateRandomString(30);

        $('#lumen_access_token_v1').val(key);
    }
   
    </script>
@section('script')



<script src="{{asset('assets/libs/dropzone/dropzone.min.js')}}"></script>
<script src="{{asset('assets/libs/dropify/dropify.min.js')}}"></script>
<script src="{{asset('assets/js/pages/form-fileuploads.init.js')}}"></script>

<script>


        
    

</script>
@endsection

<div class="modal fade" id="lumen-modal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Modal Title</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form method="POST" action="{{ route('lumen-client-save')}}">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputField1">Domain</label>
                            <input type="text" class="form-control" id="domain-name" name="domain">
                        </div>
                        <div class="form-group col-md-6">
                            <label for="inputField2">Short Code</label>
                            <input type="text" class="form-control" id="short-code" name ="code">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-md-6">
                            <label for="inputField3">Database Name</label>
                            <input type="text" class="form-control" id="database_name" name="database_name" >
                        </div>
                        <div class="form-group col-md-6 mb-3">
                            <div class="domain-outer border-0 d-flex align-items-center justify-content-between">
                                <label for="lumen_access_token_v1">V1 {{ __('API ACCESS TOKEN') }}</label>
                                <span class="text-right col-6 col-md-6"><a
                                        href="javascript: genrateKeyAndToken();">{{ __('Generate Key') }}</a></span>

                            </div>
                            <input type="text" name="lumen_access_token_v1" id="lumen_access_token_v1"
                                placeholder="kjadsasd66asdas" class="form-control"
                                value="{{ old('lumen_access_token_v1', $preference->lumen_access_token_v1 ?? '') }}">
                            @if ($errors->has('lumen_access_token_v1'))
                                <span class="text-danger" role="alert">
                                    <strong>{{ $errors->first('lumen_access_token_v1') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-row justify-content-center">
                        <div class="form-group col-md-12">
                            <button type="submit" class="btn btn-primary">Save changes</button>

                        </div>
                        
                    </div>
                </form>
            </div>
           
        </div>
    </div>
</div>
