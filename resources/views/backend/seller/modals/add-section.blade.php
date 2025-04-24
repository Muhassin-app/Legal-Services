<style>
#vendor_section_options .option_section{
    background-color: #f3f7f9;
    border-radius: 15px;
    padding-top: 15px;
    margin-bottom: 15px;
}

</style>
{{-- :nth-child(even)  --}}
<div id="add_section" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header border-bottom">
               
                <h4 class="modal-title" id="header_title">{{ __("Add Section") }} </h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
            </div>
            <form id="save_vendor_section_form" class="al_overall_form" method="post" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="vendor_id" value="{{ $vendor->id }}">
                <input type="hidden" name="section_id" value="">
                <div class="modal-body" >
                    <div class="row">
                        
                            <div class="col-md-7">
                                <div class="form-group"  id="headingInput">
                                    {!! Form::label('title', __('Section Heading'),['class' => 'control-label']) !!}
                                    <input type="text" name="heading" id="heading" placeholder="" class="form-control" >
                                    <span class="invalid-feedback" role="alert">
                                        <strong></strong>
                                    </span>
                                </div>
                            </div>
                            <div class="col-md-5">
                                {!! Form::label('title', __('Select Language'),['class' => 'control-label']) !!}
                                <select class="form-control" name="language_id" id="client_language">
                                
                                    @foreach($languages as $langs)
                                    <option value="{{ $langs->langId }}">{{ $langs->langName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        
                        {{-- <div class="col-md-12 selector-option-al ">
                            <h4>{{ __('Section Heading') }}</h4>
                            <table class="table table-borderless table-responsive al_table_responsive_data mb-0 optionTableAdd" id="selector-datatable">
                                <tr class="trForClone">

                                    @foreach($languages as $langs)
                                        <th>{{$langs->langName}}</th>
                                    @endforeach
                                    <th></th>
                                </tr>
                                <tbody id="table_body">
                                        <tr>
                                        @foreach($languages as $key => $vendor_langs)
                                            <td>
                                                <input class="form-control" name="language_id[{{$key}}]" type="hidden" value="{{$vendor_langs->langId}}">
                                                <input class="form-control" name="heading[{{$key}}]" type="text" id="heading_{{$vendor_langs->langId}}">
                                            </td>
                                        @endforeach
                                    <td class="lasttd"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div> --}}
                        <h4 class="pl-2">{{ __("Sub Section") }} </h4>
                        <div id='vendor_section_options' class="col-md-12">

                        </div>
                        
                       

                    
                          
                         
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info waves-effect waves-light " submitEditForm id="add_vendor_section_form">{{ __('Submit') }}</button>
                </div>
            </form>
           
        </div>
    </div>
</div>

<script type="text/template" id="vendor_section_template">
    <div class ="option_section mb-3 " id ="option_section_<%= id %>" data-section_number="<%= id %>">
        <input type="hidden" name="section_old_ids[]"  value ="<%= data?data.id:'' %>">
        <div class="form-group ">
            <div class="px-2">
                <label for="option_title_<%= id %>">{{__('Title')}}</label>
                <input type="hidden" name="section_translation_ids[]" id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
                <input type="text" name="title[]" class="form-control option_title" requrid id="question<%= id %>" placeholder="{{__('Enter Title')}}" data-id ="<%= id %>" value ="<%= data?data.title:'' %>">
            </div>
        </div>
        <div class="form-group ">
            <div class="px-2 pb-2">
                <label for="description<%= id %>">{{__('Description')}}</label>
                <input type="text" name="description[]" class="form-control answer" requrid id="description<%= id %>" placeholder="{{__('Enter Description')}}" data-id ="<%= id %>" value ="<%= data?data.description:'' %>">
            </div>
        </div>
        <div class="px-2">
            <button type="button" class="btn btn-primary add_more_button mb-3" id ="add_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> + {{__('Add')}}</button>
            <% if(id > 1) { %>
            <button type="button" class="btn btn-danger remove_more_button mb-3" id ="remove_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> - {{__('Remove')}}</button>
            <% } %>
        </div>
    </div>
   
</script>

<script type="text/template" id="vendor_section_template2">

    <div class ="option_section outer_box px-3 py-2 mb-3 " id ="option_section_<%= id %>" data-section_number="<%= id %>">
        <div class="form-group px-2">
            <div class="px-2">
                <label for="option_title_<%= id %>">{{__('Title')}}</label>
                <input type="hidden" name="title[]" id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
                <input type="text" name="title[]" class="form-control option_title" requrid id="question<%= id %>" placeholder="{{__('Enter Title')}}" data-id ="<%= id %>" value ="<%= data?data.title:'' %>">
            </div>
        </div>
        <div class="form-group px-2">
            <div class="px-2">
                <label for="description<%= id %>">{{__('description')}}</label>
                <input type="text" name="description[]" class="form-control answer" requrid id="description<%= id %>" placeholder="{{__('Enter description')}}" data-id ="<%= id %>" value ="<%= data?data.description:'' %>">
            </div>
        </div>
        <div class="px-2">
            <button type="button" class="btn btn-primary add_more_button mb-3" id ="add_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> + {{__('Add Question')}}</button>
            <% if(id > 1) { %>
            <button type="button" class="btn btn-danger remove_more_button mb-3" id ="remove_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> - {{__('Remove Question')}}</button>
            <% } %>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="outer_box px-3 py-2 mb-3">
            <div class="row rowYK">
                <h4 class="col-md-12"> <%= language?language.langName:''  %>{{ __(' Language') }}</h4>
                <input type="hidden" name="option_language_id[]" id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= language?language.id:'' %>">
                <div class="col-md-12">
                    <div class="form-group" id="">
                        <label for="option_title_<%= id %>">{{__('Title')}}</label>
                        <input type="hidden" name="title[]" id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
                        <input type="text" name="title[]" class="form-control option_title" requrid id="question<%= id %>" placeholder="{{__('Enter Title')}}" data-id ="<%= id %>" value ="<%= data?data.title:'' %>">
                        <span class="invalid-feedback" role="alert">
                            <strong></strong>
                        </span>
                    </div>
                </div>
               
                <div class="col-md-12">
                    <div class="form-group" id="descriptionInput">
                        <label for="description<%= id %>">{{__('description')}}</label>
                        <input type="text" name="description[]" class="form-control answer" requrid id="description<%= id %>" placeholder="{{__('Enter description')}}" data-id ="<%= id %>" value ="<%= data?data.description:'' %>">
                      </div>
                </div>
                
            </div>
        </div>
    </div>
</script>

