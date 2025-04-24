<div class="col-6">
    <div class="">
        <div class="influencer-form-list">
            <div class="">
                <div class="card-box h-100">
                    <div class="row mb-2">
                        <div class="col-sm-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <h4 class="page-title">{{ ("Influencer Tiers") }}</h4>
                                <button class="btn btn-info waves-effect waves-light text-sm-right addTierbtn" dataid="0"><i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
                                </button>
                            </div>
                            
                        </div>
                    </div>
                    <div class="row variant-row">
                        <div class="col-md-12">
                            
                            <div class="table-responsive outer-box">
                                <table class="table table-centered table-nowrap table-striped" id="varient-datatable">
                                    <thead>
                                        <tr>
                                            <th>{{ __('Name') }}</th>
                                            <th>{{ __('Target') }}</th>
                                            <th>{{ __('Commision Type') }}</th>
                                            <th>{{ __('Commision') }}</th>
                                            <th>{{ __('Status') }}</th>
                                            <th>{{ __('Action') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        @if(!empty($influencer_tier_list))
                                        @foreach($influencer_tier_list as $key => $tier)

                                            
                                                <tr class="variantList" data-row-id="{{$tier->id}}">
                                                    <td>
                                                        <a class="editTierBtn" dataid="{{$tier->id}}" href="javascript:void(0);">{{$tier->name}}</a>
                                                    </td>
                                                    <td>
                                                        {{$tier->target}}
                                                    </td>
                                                    <td>
                                                        {{(@$tier->commision_type==1)?'Percentage':'Fixed'}}
                                                    </td>
                                                    <td>
                                                        {{$tier->commision}}
                                                    </td>
                                                    <td>
                                                        {{(@$tier->status)?'Active':'Inactive'}}
                                                    </td>
                                                    
                                                   <td>
                                                     <a class="action-icon editTierBtn" dataid="{{$tier->id}}" href="javascript:void(0);">
                                                            <i class="mdi mdi-square-edit-outline"></i>
                                                    </a>
                                                    </td>
                                                    
                                                </tr>
                                            
                                        @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="addTiermodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Tier") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addTierForm" method="post" enctype="multipart/form-data" action="{{route('tier.store')}}">
                @csrf
                <div class="modal-body" id="AddTierBox">
                    <div class="row">
                        
                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Name'),['class' => 'control-label']) !!}
                            {!! Form::text('name', '',['class' => 'form-control', 'placeholder' => 'Tier Name', 'required'=>'required']) !!}
                        </div>
                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Target'),['class' => 'control-label']) !!}
                            {!! Form::number('target', '0',['class' => 'form-control', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => 'Target Tier', 'required'=>'required']) !!}
                        </div>
                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Commision Type'),['class' => 'control-label']) !!}
                            <select class="selectize-select form-control" name="commision_type" required>
                               
                                <option value="1">{{__('Percentage')}}</option>
                                <option value="2">{{__('Fixed')}}</option>
                               
                            </select>
                        </div>
                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Commision'),['class' => 'control-label']) !!}
                            {!! Form::number('commision', '0',['class' => 'form-control', 'min' => '1', 'onkeypress' => 'return isNumberKey(event)', 'placeholder' => 'Commision', 'required'=>'required']) !!}
                        </div>

                        <div class="col-sm-12 mb-2">
                            {!! Form::label('title', __('Status'),['class' => 'control-label']) !!}
                            <select class="selectize-select form-control" name="status" required>
                                <option value="1">{{__('Active')}}</option>
                                <option value="0">{{__('Inactive')}}</option>
                            </select>
                        </div>
                        
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addTierSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editTiermodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Tier')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="outter-loader d-none"><div class="css-loader"></div></div>
            <form id="editTierForm" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editTierBox">
                    
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light editTierSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
$(".addTierbtn").click(function(e) {
        console.log('click function called');
        $('#addTiermodal').modal({
            backdrop: 'static',
            keyboard: false
        });

    });

    // Edit Influencer Attribute
$('.editTierBtn').on('click', function(e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
        }
    });
    e.preventDefault();
    var did = $(this).attr('dataid');
    $.ajax({
        type: "get",
        url: "{{url('client/tier')}}" + '/' + did + '/edit',
        data: '',
        dataType: 'json',
        beforeSend: function() {
            $(".loader_box").show();
        },
        success: function(data) {
            $('#editTiermodal').modal({
                backdrop: 'static',
                keyboard: false
            });
            
            $('#editTierForm #editTierBox').html(data.html);
           
            $('.selectize-select').selectize();
           
            
            document.getElementById('editTierForm').action = data.submitUrl;
        },
        error: function(data) {
            console.log('data2');
        },
        complete: function() {
            $('.loader_box').hide();
        }
    });
});

</script>