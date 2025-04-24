@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Influencer'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
@endsection

@section('content')

<!-- Start Content-->
<div class="container-fluid">

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">Add/Edit Influencer</h4>
            </div>
        </div>
    </div>
    <!-- end page title -->
    <div class="row d-flex justify-content-center">
        <div class="col-6">
            <div class="card">
                <div class="card-body">
                    @if( !empty($influence_edit) )
                        <form action="{{ route('influencer-refer-earn.update', ['id' => $influence_edit->id]) }}" method="POST">
                    @else
                        <form action="{{ route('influencer-refer-earn.store') }}" method="POST">
                    @endif
                        @csrf
                        <div class="form-group">
                            <label for="">Name</label>
                            <input type="text" name="name" id="name" class="form-control" value="{{ printOldOrDbValue('name', $influence_edit) }}"/>
                            @error('name')
                            <div class="error">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="">KYC</label>
                            
                            <input type="checkbox" name="kyc" id="kyc"  value="1" @if(@$influence_edit->kyc) checked @endif/>
                            @error('kyc')
                            <div class="error">{{ $message }}</div>
                            @enderror
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Save</button>
                        <a href="{{ route('influencer-refer-earn.index') }}" class="btn btn-primary btn-sm">Cancel</a>
                    </form>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
</div>



@endsection