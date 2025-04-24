@extends('layouts.vertical', ['title' => 'Manage Attributes']) @section('css')
<link
	href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css"
	rel="stylesheet">
<link href="{{asset('assets/libs/nestable2/nestable2.min.css')}}"
	rel="stylesheet" type="text/css" />

<style>
.select-category-lang label {
	font-size: 12px;
}

.select-category-lang select {
	height: 36px;
}

.modal-category-list {
	height: auto;
	overflow-y: hidden;
	flex-wrap: nowrap;
	width: 100%;
	max-width: 100%;
	margin: 0 auto;
}

.modal-category-list .col-sm {
	width: auto;
	max-width: 25%;
	min-width: 25%;
}

.category-modal-right {
	height: 100%;
	max-height: 650px;
	min-height: 650px;
	overflow-x: auto;
	overflow-y: scroll;
	border: 1px solid #eeeeeeab;
}
/*for custom  scrollbar css */
.category-modal-right::-webkit-scrollbar-track {
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, 0.3);
	border-radius: 10px;
	background-color: #F5F5F5;
}

.category-modal-right::-webkit-scrollbar {
	width: 12px;
	background-color: #F5F5F5;
}

.category-modal-right::-webkit-scrollbar-thumb {
	border-radius: 10px;
	-webkit-box-shadow: inset 0 0 6px rgba(0, 0, 0, .3);
	background-color: #D62929;
}
/*------end here*/
#edit-category-form .modal-dialog.modal-dialog-centered.modal-md {
	max-width: 800px;
}

#add-category-form .modal-dialog.modal-dialog-centered.modal-md {
	max-width: 800px;
}

.Category-select_option select {
	border: none;
	padding-left: 0px;
	font-size: 14px;
	font-weight: 600;
	color: #6c757d;
}

.modal-category-list .select-category label:before {
	clip-path: polygon(0 0, 0 50%, 35% 0);
	font-size: 22px;
	padding: 2px 2px;
}

.modal-category-list .select-category label::after {
	display: none;
}

.modal-category-list .form-check-input:checked ~label .category-img::after
	{
	opacity: 1;
}

.modal-category-list .select-category label .category-img {
	position: relative;
}

.modal-category-list .select-category label .category-img:after {
	background: rgb(0 0 0/ 31%);
	background-image: none !important;
	width: 100%;
	height: 100%;
	left: 0;
	top: 0;
	opacity: 0;
	content: "";
	position: absolute;
}

.category-icon-image .dropify-wrapper {
	height: 70px;
}

.modal-category-list .select-category .modal-category-btm-title h6 {
	text-align: left;
	font-size: 14px;
	margin: 0px;
	padding: 2px 0px;
}

.modal-category-list .select-category .modal-category-btm-title p {
	font-size: 12px;
}

.modal-category-list .select-category .modal-category-btm-title p i {
	font-size: 13px;
}

.modal-category-list .edit-cart-text input {
	font-size: 10px;
	border: 1px solid #eee;
	padding: 4px 10px;
	box-shadow: 1px 3px 4px #eee;
	border-radius: 8px;
	color: #9b9494;
	font-weight: 100;
}
.alHeightAutoScrooll {
	height: 300px;
	overflow: auto;
}

.alSmHeight {
	height: 150px;
	overflow: auto;
}
.containerd .page-title-box h4 {
    font-weight: 600 !important;
    text-transform: capitalize;
    letter-spacing: 0.2px;
}
.containerd .page-title-box {
    margin-top: 20px;
}

</style>
@endsection @section('content')
<div class="containerd alCatalogPage">
	<div class="row">
		<div class="col-12">
			<div class="page-title-box">
				<h4 class="page-title">{{ __('Manage Attributes') }}</h4>
			</div>
		</div>
		<div class="col-sm-12 text-sm-left">
			<div class="alert alert-success deletecategorymsg"
				style="display: none">
				<span></span>
			</div>
			@if (\Session::has('success'))
			<div class="alert alert-success">
				<span>{!! \Session::get('success') !!}</span>
			</div>
			@endif @if (\Session::has('error_delete'))
			<div class="alert alert-danger">
				<span>{!! \Session::get('error_delete') !!}</span>
			</div>
			@endif
		</div>
	</div>
	<div class="">
		<div class="card-box h-100">
			<div class="row mb-2">
				<div class="col-sm-12">
					<div class="d-flex justify-content-between align-items-center">
						<h4 class="page-title">{{ ("Attribute") }}</h4>
						<button
							class="btn btn-info waves-effect waves-light text-sm-right addAttributebtn"
							dataid="0">
							<i class="mdi mdi-plus-circle mr-1"></i> {{ __('Add') }}
						</button>
					</div>
					<p class="sub-header">{{ __("Drag & drop Attribute to change the
						position") }}</p>
				</div>
			</div>
			<div class="row variant-row">
				<div class="col-md-12">
					<form name="variant_order" id="variant_order"
						action="{{route('variant.order')}}" method="post">
						@csrf <input type="hidden" name="orderData" id="orderVariantData"
							value="" />
					</form>
					<div class="table-responsive outer-box">
						<table class="table table-centered table-nowrap table-striped"
							id="varient-datatable">
							<thead>
								<tr>
									<th>#</th>
									<th>{{ __('Name') }}</th>
									<th>{{ __('Options') }}</th>
									<th>{{ __('Service Type') }}</th>
									<th>{{ __('Field Type') }}</th>
									<th>{{ __('Attribute Type') }}</th>
									<th>{{ __('Is Required?') }}</th>
									<th>{{ __('Action') }}</th>
								</tr>
							</thead>
							<tbody>

								@foreach($attributes as $key => $variant)
								@if(!empty($variant->translation_one))
								<tr class="variantList" data-row-id="{{$variant->id}}">
									<td><span class="dragula-handle"></span></td>
									<td><a class="editAttributeBtn" dataid="{{$variant->id}}"
										href="javascript:void(0);">{{$variant->title}}</a> <br> <b>{{isset($variant->varcategory->cate->primary->name)
											? $variant->varcategory->cate->primary->name : ''}}</b></td>
									<td>@foreach($variant->option as $key => $value) <label
										style="margin-bottom: 3px;"> @if(isset($variant) &&
											!empty($variant->type) && $variant->type == 2) <span
											style="padding: 8px; float: left; border: 1px dotted #ccc;">
										</span> @endif &nbsp;&nbsp; {{$value->title}}
									</label> <br /> @endforeach
									</td>
									<td>
										{{$variant->service_type}}
									</td>
									<td>
										{{$variant->fieldType($variant->field_type)}}
									</td>
									<td>
										{{((@$variant->attributeType)?$variant->attributeType->title:'')}}
									</td>
									<td>{{ ($variant->is_required == 1)?__('Yes'):__('No') }}</td>
									<td><a class="action-icon editAttributeBtn"
										dataid="{{$variant->id}}" href="javascript:void(0);"> <i
											class="mdi mdi-square-edit-outline"></i>
									</a> @if( auth()->user()->is_superadmin ) <a
										class="action-icon deleteAttribute" dataid="{{$variant->id}}"
										href="javascript:void(0);"> <i class="mdi mdi-delete"></i>
									</a>
										<form action="{{route('manage.attribute.delete', $variant->id)}}"
											method="POST" style="display: none;"
											id="attrDeleteForm{{$variant->id}}">
											@csrf @method('DELETE')
											<button type="submit"
												class="action-icon btn btn-primary-outline"
												dataid="{{$variant->id}}"
												onclick="return confirm('Are you sure? You want to delete the attribute.')">
												<i class="mdi mdi-delete"></i>
											</button>
										</form> @endif</td>
								</tr>
								@endif @endforeach

							</tbody>
						</table>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>



<!-- MODALS -->


<div id="addAttributemodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Attribute")}}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addAttributeForm" method="post" enctype="multipart/form-data" action="{{route('manage.attribute.store')}}">
                @csrf
                <div class="modal-body" id="AddAttributeBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addAttributeSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>


<div id="editAttributemodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit Attribute") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="outter-loader d-none"><div class="css-loader"></div></div>
            <form id="editAttributeForm" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editAttributeBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light editAttributeSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection 
@section('script')
<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.10.3/jquery-ui.min.js"></script>
<script src="{{asset('assets/js/jscolor.js')}}"></script>
@include('backend.catalog.pagescript')
<script type="text/javascript">
	$(".addAttributebtn").click(function(e) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
            }
        });
        e.preventDefault();
        var did = $(this).attr('dataid');
        $.ajax({
            type: "get",
            url: "{{route('manage.attribute.add')}}",
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
            url: "{{url('client/attributes/edit')}}" + '/' + did,
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
</script>

@endsection
