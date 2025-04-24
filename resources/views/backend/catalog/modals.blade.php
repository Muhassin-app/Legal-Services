<div id="addVariantmodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add ".getNomenclatureName('Variant')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addVariantForm" method="post" enctype="multipart/form-data" action="{{route('variant.store')}}">
                @csrf
                <div class="modal-body" id="AddVariantBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addVariantSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editVariantmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Variant')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="editVariantForm" method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-body" id="editVariantBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addVariantSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--   Brand      modals   -->
<div id="addBrandmodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add Brand") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addBrandForm" method="post" enctype="multipart/form-data" action="{{route('brand.store')}}">
                @csrf
                <div class="modal-body" id="AddbrandBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light addbrandSubmit">{{ __("Submit") }}</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="editBrandmodal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Edit Brand") }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="editBrandForm" method="post" enctype="multipart/form-data" action="">
                @method('PUT')
                @csrf
                <div class="modal-body" id="editBrandBox">

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-info waves-effect waves-light editbrandSubmit">{{ __('Submit') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- modal for product tags -->
   <div id="add_product_tag_modal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="standard-modalLabel" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
         <div class="modal-content">
            <div class="modal-header border-bottom">
               <h4 class="modal-title" id="standard-modalLabel">{{ __("Add Product Tag") }}</h4>
               <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <div class="modal-body">
               <form id="productTagForm" method="POST" action="javascript:void(0)" enctype="multipart/form-data">
                  @csrf
                  <div id="save_product_tag">
                     <input type="hidden" name="tag_id" value="">
                     <div class="row">
                        <div class="col-md-3">
                           <label>{{ __('Upload Icon') }}</label>
                           <input type="file" accept="image/*" data-plugins="dropify" name="icon" class="dropify"  />
                           <label class="logo-size text-right w-100">{{ __("Icon Size") }} 100X100</label>
                       </div>
                    </div>
                        <div class="row">
                            <div class="col-12 selector-option-al ">
                                <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                    <tr class="trForClone">

                                        @foreach($languages as $lang)
                                            <th>{{isset($lang->language)?$lang->language->name:'N/A'}}</th>
                                        @endforeach
                                        <th></th>
                                    </tr>
                                    <tbody >
                                        <tr>
                                        @foreach($languages as $key => $lang)
                                            <td>
                                                <input class="form-control" name="language_id[{{$key}}]" type="hidden" value="{{$lang->language_id}}">
                                                <input class="form-control" name="name[{{$key}}]" type="text" id="product_tag_name_{{$lang->language_id}}">
                                            </td>
                                            @if($key == 0)
                                            <span class="text-danger error-text product_tag_err"></span>
                                            @endif
                                            @endforeach
                                            <td class="lasttd"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>


                  </div>
               </form>
            </div>
            <div class="modal-footer">
               <button type="button" class="btn btn-primary submitSaveProductTag">{{ __("Save") }}</button>
            </div>
         </div>
      </div>
   </div>

{{-- Attribute Modal --}}
<div id="addAttributemodal" class="modal al fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
                <h4 class="modal-title">{{ __("Add ".getNomenclatureName('Attribute')) }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="addAttributeForm" method="post" enctype="multipart/form-data" action="{{route('attribute.store')}}">
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
                <h4 class="modal-title">{{ __("Edit ".getNomenclatureName('Attribute')) }}</h4>
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
