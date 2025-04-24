@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Pages'])

@section('css')
<link href="{{asset('assets/libs/dropzone/dropzone.min.css')}}" rel="stylesheet" type="text/css" />
<link href="{{asset('assets/libs/dropify/dropify.min.css')}}" rel="stylesheet" type="text/css" />
<!-- <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet"> -->
<link rel="stylesheet" href="{{ asset('assets/ck_editor/samples/css/samples.css') }}">
<link rel="stylesheet" href="{{ asset('assets/ck_editor/samples/toolbarconfigurator/lib/codemirror/neo.css') }}">
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-8">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Pages") }}</h4>
            </div>
        </div>
    </div>
    <div class="row cms-cols al_custom_cms_page">
        <div class="col-md-5 col-xl-3 mb-2">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4>{{ __("List") }}</h4>
                        <button class="btn btn-info add_cms_page" data-toggle="modal">
                            <i class="mdi mdi-plus-circle"></i> {{ __("Add") }}
                        </button>
                    </div>
                   {{-- <div class="table-responsive pages-list-data">
                        <table class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">{{ __("Page Name") }}</th>
                                    <th class="text-right border-bottom-0">{{ __("Action") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pages as $page)
                                    <tr class="page-title active-page page-detail" data-page_id="{{$page->id}}" data-show_url="{{route('cms.page.show', ['id'=> $page->id])}}" data-active_url="{{route('extrapage',['slug' => $page->slug])}}">
                                        <td>
                                            <a class="text-body" href="javascript:void(0)" id="text_body_{{$page->id}}">{{$page->primary ? $page->primary->title : ''}}</a>
                                        </td>
                                        <td align ="right">
                                            <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_BLANK">
                                                <i class="mdi mdi-eye"></i>
                                            </a>
                                            @if(!in_array($page->id, [1,2,3]))
                                                <a class="text-body delete-page" href="javascript:void(0)" data-page_id="{{$page->id}}">
                                                    <i class="mdi mdi-delete"></i>
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                   </div> --}}
                   <div class="custom-dd-empty dd home-options-list" id="pickup_page_datatable">
                    <ol class="dd-list p-0" id="page_ol" >
                        @forelse($pages as $page)
                        <li class="dd-item dd3-item d-flex align-items-center page-detail on_click{{$page->slug}}" data-id="1" data-row-id="{{$page->id}}" data-page_id="{{$page->id}}" data-show_url="{{route('cms.page.show', ['id'=> $page->id])}}" data-active_url="{{route('extrapage',['slug' => $page->slug])}}">
                            <a herf="#" class="dd-handle dd3-handle d-block mr-auto" id="text_body_{{$page->id}}">
                                {{$page->primary ? $page->primary->title : ''}}

                                <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_BLANK" class="mr-2">
                                    <!-- <i class="mdi mdi-eye"></i> -->
                                    <svg style="height:14px;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 14.34 12"><defs><style>.cls-1{fill:#6e768e;}</style></defs><g id="Layer_2" data-name="Layer 2"><g id="Layer_1-2" data-name="Layer 1"><path class="cls-1" d="M2.69,10.8c-.29,0-.58,0-.86,0a.6.6,0,0,1-.63-.64V9.31a.63.63,0,0,0-.42-.58.61.61,0,0,0-.65.2A2.53,2.53,0,0,0,0,9.15v1.29l0,.08a1.67,1.67,0,0,0,1.34,1.41,5.84,5.84,0,0,0,1.45,0,.57.57,0,0,0,.34-.21.54.54,0,0,0,.07-.61A.58.58,0,0,0,2.69,10.8Z"/><path class="cls-1" d="M0,2.9a.6.6,0,0,0,.69.41.61.61,0,0,0,.48-.65V1.88a.59.59,0,0,1,.64-.65h.77a1.09,1.09,0,0,0,.26,0A.59.59,0,0,0,3.28.56.55.55,0,0,0,2.75,0,9.55,9.55,0,0,0,1.68,0,1.7,1.7,0,0,0,.22,1,4.44,4.44,0,0,0,0,1.58V2.87Z"/><path class="cls-1" d="M11.91,5.84a4.17,4.17,0,0,0-1.54-2.36A5.09,5.09,0,0,0,4.75,3,4.32,4.32,0,0,0,2.41,5.89a.57.57,0,0,0,0,.26,4.09,4.09,0,0,0,.78,1.61A4.87,4.87,0,0,0,7,9.65a5.43,5.43,0,0,0,2.15-.39,4.55,4.55,0,0,0,2.76-3A.77.77,0,0,0,11.91,5.84Zm-4.74,2A1.8,1.8,0,1,1,9,6,1.82,1.82,0,0,1,7.17,7.81Z"/><path class="cls-1" d="M11.67,1.23c.28,0,.57,0,.85,0a.58.58,0,0,1,.61.63c0,.29,0,.58,0,.87a.6.6,0,1,0,1.19,0v-.9A1.76,1.76,0,0,0,13,.1a5.54,5.54,0,0,0-.83-.1V0h-.61a.49.49,0,0,0-.41.23.58.58,0,0,0-.06.62A.6.6,0,0,0,11.67,1.23Z"/><path class="cls-1" d="M14.32,9.18a0,0,0,0,0,0,0,.59.59,0,0,0-1.17.15c0,.28,0,.57,0,.85a.59.59,0,0,1-.64.65h-.77a1.09,1.09,0,0,0-.26,0,.57.57,0,0,0-.4.64.56.56,0,0,0,.53.52h.94a1.8,1.8,0,0,0,1.73-1.28A5.69,5.69,0,0,0,14.32,9.18Z"/></g></g></svg>

                                </a>
                                @if(!in_array($page->id, [1,2,3]))
                                    <a class="text-body delete-page" href="javascript:void(0)" data-page_id="{{$page->id}}">
                                        <i class="mdi mdi-delete"></i>

                                    </a>
                                @endif
                            </a>
                        </li>
                        @empty
                        @endforelse
                    </ol>
                   </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xl-6 mb-2 cms-content">
            <div class="card">
                <div class="card-body p-3" id="edit_page_content">
                    <div class="row">
                        <div class="col-12">
                            <h4 class="page-title mt-0">
                                <input class="form-control mb-2" id="edit_title" name="edit_title" type="text">
                            </h4>
                        </div>
                    </div>
                    <div class="row align-items-center">
                        <div class="col-md-12 col-xl-6 mb-2">
                            <!-- <label for="title" class="control-label">{{ __("Title") }}</label> -->
                            <!-- <input class="form-control" id="edit_title" name="meta_title" type="text"> -->
                            <div class="site_link position-relative px-0">
                                @if(isset($page) && !empty($page))
                                <a href="{{route('extrapage',['slug' => $page->slug])}}" target="_blank"><span id="pwd_spn" class="password-span">{{route('extrapage',['slug' => $page->slug])}}</span></a>
                                <label class="copy_link float-right" id="cp_btn" title="copy">
                                    <img src="{{ asset('assets/icons/domain_copy_icon.svg')}}" alt="">
                                    <span class="copied_txt" id="show_copy_msg_on_click_copy" style="display:none;">Copied</span>
                                </label>
                                @endif
                            </div>
                            <span class="text-danger error-text updatetitleError"></span>
                        </div>
                        <div class="col-md-4 col-xl-2 mb-2">
                            <div class="form-group mb-0">
                                <select class="form-control" id="client_language">
                                   @foreach($client_languages as $client_language)
                                    <option value="{{$client_language->langId}}">{{$client_language->langName}}</option>
                                   @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4  col-xl-2 mb-2">
                            <div class="form-group mb-0">
                                <select class="form-control" id="published">
                                    <option value="0">{{ __("Draft") }}</option>
                                    <option value="1">{{ __("Publish") }}</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4 col-xl-2 text-right mb-2">
                            <button type="button" class="btn btn-info w-100" id="update_page_btn"> {{ __("Update") }}</button>
                        </div>
                        <div class="col-12">
                            <div class="row">
                                <div class="col-6 mb-2">
                                    <label for="title" class="control-label">{{ __("Meta Title") }}</label>
                                    <input class="form-control" id="edit_meta_title" name="meta_title" type="text">
                                </div>
                                <div class="col-6 mb-2">
                                    <label for="title" class="control-label">{{ __("Type") }}</label>
                                    <select class="form-control" name="type_of_form" id="type_of_form">
                                        <option value="0">{{__("None")}}</option>
                                        <option value="1">{{__("Vendor Registration")}}</option>
                                        <option value="2">{{__("Driver Registration")}}</option>
                                        <option value="3">{{__("Faq's")}}</option>
                                        <option value="4">{{__("Privacy Policy")}}</option>
                                        <option value="5">{{__("Terms & Conditions")}}</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <input type="hidden" id="page_id" value="">
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <label for="title" class="control-label">{{ __("Meta Keyword") }}</label>
                                    <textarea class="form-control m-0" id="edit_meta_keyword" rows="1" name="meta_keyword" cols="10"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <label for="title" class="control-label">{{ __("Meta Description") }}</label>
                                    <textarea class="form-control m-0" id="edit_meta_description" rows="1" name="meta_description" cols="10"></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 al_custom_cke">
                            <label for="title" class="control-label mb-0 ">{{ __("Description") }}</label>
                            <textarea class="form-control" id="edit_description" rows="9" name="meta_description" cols="100"></textarea>
                            <span class="text-danger error-text updatedescrpitionError"></span>
                        </div>
                        <div class="col-md-12 d-none" id="faqSection">
                            <div class="card_">
                                <div class="card-body_ p-0">
                                    <div class="d-flex align-items-center justify-content-between mb-3 px-2">
                                        <h4>{{ __("Faq's") }}</h4>
                                    </div>
                                    <div class="faq_section" id ="faq_show_section"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/template" id="faq_template">
    <div class ="option_section" id ="option_section_<%= id %>" data-section_number="<%= id %>">
        <div class="form-group px-2">
            <div class="px-2">
                <label for="option_title_<%= id %>">{{__('Question')}}</label>
                <input type="hidden" name="question_id[]"  id="option_id<%= id %>" data-id ="<%= id %>" value ="<%= data?data.id:'' %>">
                <input type="text" name="question[]" class="form-control option_title" requrid id="question<%= id %>" placeholder="{{__('Enter question')}}" data-id ="<%= id %>" value ="<%= data?data.question:'' %>">
            </div>
        </div>
        <div class="form-group px-2">
            <div class="px-2">
                <label for="answer<%= id %>">{{__('Answer')}}</label>
                <input type="text" name="answer[]" class="form-control answer" requrid id="answer<%= id %>" placeholder="{{__('Enter Answer')}}" data-id ="<%= id %>" value ="<%= data?data.answer:'' %>">
            </div>
        </div>
        <div class="px-2">
            <button type="button" class="btn btn-primary add_more_button mb-3" id ="add_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> + {{__('Add Question')}}</button>
            <% if(id > 1) { %>
            <button type="button" class="btn btn-danger remove_more_button mb-3" id ="remove_button_<%= id %>" data-id ="<%= id %>" style=" margin-top: 17px;"> - {{__('Remove Question')}}</button>
            <% } %>
        </div>
    </div>
    </script>
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script src="{{ asset('assets/ck_editor/samples/js/sample.js')}}"></script>
<script src="{{ asset('front-assets/js/underscore.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {


         $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });

        setTimeout(function(){
            $('li.page-detail:first').trigger('click');
        }, 500);
        setTimeout(function(){
            $('tr.page-title:first').trigger('click');
        }, 500);
        $(document).on("change","#client_language",function() {
            let page_id = $('#edit_page_content #page_id').val();
            $('#text_body_'+page_id).trigger('click');
        });

        $(document).on("click",".page-detail",function() {
            var section_id = 0
            // $('#edit_page_content #edit_description').val('');
            // $('#edit_page_content #edit_description').summernote('destroy');
            let url = $(this).data('show_url');
            let active_url = $(this).data('active_url');
            $('#edit_page_content .password-span').html(active_url);
            $('#edit_page_content .password-span').closest('a').attr('href',active_url);

            let language_id = $('#edit_page_content #client_language :selected').val();
            $.get(url, {language_id:language_id},function(response) {
              if(response.status == 'Success'){
                if(response.data){
                    $('#edit_page_content #page_id').val(response.data.id);
                    if(response.data.translation){
                        if(response.data.translation.type_of_form==3){
                            console.log("language change");
                            $('.option_section').remove();
                            $("#faqSection").removeClass("d-none");

                            var faqs = response.data.faqs;
                            var faq_section_temp    = $('#faq_template').html();
                            var modified_temp         = _.template(faq_section_temp);
                            var section_id = 0
                            $(faqs).each(function(index, value) {
                                section_id                = parseInt(section_id);
                                section_id                = section_id +1;
                                $('#faq_show_section').append(modified_temp({ id:section_id,data:value}));
                                $('.add_more_button').hide();
                                $('#add_button_'+section_id).show();

                            });
                            addFaqSectionTemplate(section_id);
                        }else{
                            $('.option_section').remove();
                            $("#faqSection").addClass("d-none");
                        }
                        $('#edit_page_content #edit_title').val(response.data.translation.title);
                        $("#edit_page_content #published").val(response.data.translation.is_published);
                        $('#edit_page_content #edit_meta_title').val(response.data.translation.meta_title);
                        $('#edit_page_content #type_of_form').val(response.data.translation.type_of_form);
                        // $('#edit_page_content #edit_description').val(response.data.translation.description);
                        CKEDITOR.instances.edit_description.setData(response.data.translation.description);
                        $('#edit_page_content #edit_meta_keyword').val(response.data.translation.meta_keyword);
                        $('#edit_page_content #edit_meta_description').val(response.data.translation.meta_description);
                        $("#update_page_btn").html('Update');
                        // $('#edit_page_content #edit_description').summernote({'height':450});

                    }else{
                      $('.option_section').remove();
                      var selectedid = $('#type_of_form').val();
                        if(selectedid==3){
                            $("#faqSection").removeClass("d-none");
                            var classoption_section = $('#faqSection').find('.option_section');

                            if(classoption_section.length==0){
                                addFaqSectionTemplate(0);
                            }
                        }
                      $(':input:text').val('');
                      $('textarea').val('');
                    }
                }else{
                    $(':input:text').val('');
                    $('textarea').val('');
                    $('#edit_page_content #page_id').val('');
                }
              }
            });
        });
        $(document).on("click",".add_cms_page",function() {
            $('.page-heading').html('Add Page Content');
            $("#update_page_btn").html('Add');
            $('#edit_page_content #page_id').val('');
            $('#edit_page_content #edit_title').val('');
            $('#edit_page_content #edit_meta_title').val('');
            $('#edit_page_content #type_of_form').val('');
            // $('#edit_page_content #edit_description').summernote('reset');
            CKEDITOR.instances.edit_description.setData("");
            $('#edit_page_content #edit_meta_keyword').val('');
            $('#edit_page_content #edit_meta_description').val('');
            $("#faqSection").addClass("d-none");
            $('.option_section').remove();

        });
        $(document).on("click",".delete-page",function() {
            var page_id = $(this).data('page_id');
            let destroy_url = "{{route('cms.page.delete')}}";
            Swal.fire({
                title: "{{__('Are you Sure?')}}",
                icon: 'info',
                showCancelButton: true,
                confirmButtonText: 'Ok',
            }).then((result) => {
                if(result.value)
                {
                    $.ajax({
                        type: "POST",
                        dataType: 'json',
                        url: destroy_url,
                        data: {page_id: page_id},
                        success: function(response) {
                            if (response.status == "Success") {
                                $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                                setTimeout(function() {
                                    location.reload()
                                }, 2000);
                            }
                        }
                    });
                }
            });
        });
        $(document).on("click","#update_page_btn",function() {
            var update_url = "{{route('cms.page.update')}}";
            let page_id = $('#edit_page_content #page_id').val();
            if(page_id == ''){
                var update_url = "{{route('cms.page.create')}}";
            }
            let edit_title = $('#edit_page_content #edit_title').val();
            let is_published = $('#edit_page_content #published option:selected').val();
            let language_id = $('#edit_page_content #client_language :selected').val();
            let edit_meta_title = $('#edit_page_content #edit_meta_title').val();
            let type_of_form = $('#edit_page_content #type_of_form').val();
            // let edit_description = $('#edit_page_content #edit_description').val();
            let edit_description = CKEDITOR.instances.edit_description.getData();
            let edit_meta_keyword = $('#edit_page_content #edit_meta_keyword').val();
            let question = $("input[name='question[]']").map(function(){return $(this).val();}).get();
            let answer = $("input[name='answer[]']").map(function(){return $(this).val();}).get();
            let question_old_ids = $("input[name='question_id[]']").map(function(){return $(this).val();}).get();

            let edit_meta_description = $('#edit_page_content #edit_meta_description').val();
            var data = { page_id: page_id,question_old_ids:question_old_ids,answer: answer,question: question, is_published: is_published, edit_title: edit_title,edit_meta_title:edit_meta_title, edit_description:edit_description, edit_meta_keyword:edit_meta_keyword, edit_meta_description:edit_meta_description,language_id:language_id,type_of_form:type_of_form};
            $.post(update_url, data, function(response) {
              $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
              $('#text_body_'+response.data.id).html(response.data.title);
              setTimeout(function() {
                    location.reload()
                }, 2000);
            }).fail(function(response) {
                $('#edit_page_content .updatetitleError').html(response.responseJSON.errors.edit_title[0]);
                $('#edit_page_content .updatedescrpitionError').html(response.responseJSON.errors.edit_description[0]);
            });
        });
        $("#pickup_page_datatable ol").sortable({
        // placeholder: "ui-state-highlight",
            update: function(event, ui) {
                var post_order_ids = new Array();
                $('#pickup_page_datatable li').each(function() {
                    console.log($(this).data("row-id"));
                    post_order_ids.push($(this).data("row-id"));
                });
                console.log(post_order_ids);
                saveOrderPickup(post_order_ids);

            }
        });
        function saveOrderPickup(orderVal) {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('input[name="_token"]').val()
                }
            });
            $.ajax({
                type: "post",
                dataType: "json",
                url: "{{ url('client/cms/page/ordering') }}",
                data: {
                    order: orderVal
                },
                success: function(response) {
                    if (response.status == 'success') {
                        $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
                    }
                },
            });
        }
        $(document).on('change','#type_of_form',function(){
            var selectedid = $(this).val();
            if(selectedid==3){
                $("#faqSection").removeClass("d-none");
                var classoption_section = $('#faqSection').find('.option_section');
                console.log(classoption_section);
                if(classoption_section.length==0){
                    addFaqSectionTemplate(0);
                }
            }else{
                $("#faqSection").addClass("d-none");
            }

        });
        $(document).on('click','.add_more_button',function(){
            var main_id = $(this).data('id');
            addFaqSectionTemplate(main_id);
            console.log($('.add_more_button').length);
        });
        // var section_id = $("#faqSection .option_section").last().data('section_number');
        // console.log(section_id);
        //addFaqSectionTemplate(section_id);
        function addFaqSectionTemplate(section_id){
            section_id                = parseInt(section_id);
            section_id                = section_id +1;
            var data                  = '';
            //console.log(section_id);
            var price_section_temp    = $('#faq_template').html();
            var modified_temp         = _.template(price_section_temp);
            var result_html           = modified_temp({id:section_id,data:data});
            $("#faq_show_section").append(result_html);
            $('.add_more_button').hide();
            $('#add_button_'+section_id).show();
        }
        $(document).on('click','.remove_more_button',function(){
            var main_id =$(this).data('id');
            removeFaqSectionTemplate(main_id);
            $('.add_more_button').each(function(key,value){
                if(key == ($('.add_more_button').length-1)){
                    $('#add_button_'+$(this).data('id')).show();
                }
            });
        });
        function removeFaqSectionTemplate(div_id){
            $('#option_section_'+div_id).remove();
        }
    });
</script>
<script>
    $(document).on('click', '.copy_link', function() {
        var $temp = $("<input>");
        $("body").append($temp);
        $temp.val($('#pwd_spn').text()).select();
        document.execCommand("copy");
        $temp.remove();
        $("#show_copy_msg_on_click_copy").show();
        setTimeout(function() {
            $("#show_copy_msg_on_click_copy").hide();
        }, 1000);
    })
</script>
@endsection
@section('script')
<!-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> -->
<script>
    CKEDITOR.replace('edit_description');
    CKEDITOR.config.height = 450;
</script>
@endsection
