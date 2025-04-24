@extends('layouts.vertical', ['demo' => 'creative', 'title' => 'Notifications'])
@section('css')
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.css" rel="stylesheet">
<style>
    textarea.form-control {
    height: auto !important;
}
</style>
@endsection
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <h4 class="page-title">{{ __("Notifications") }}</h4>
            </div>
        </div>
    </div>
    <div class="row cms-cols">
        <div class="col-lg-5 col-xl-3 mb-2">
            <div class="card">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h4>{{ __("List") }}</h4>
                    </div>
                   <div class="table-responsive pages-list-data">
                        <table class="table table-striped w-100">
                            <thead>
                                <tr>
                                    <th class="border-bottom-0">{{ __("Notification Name") }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($notification_templates as $notification_template)
                                    <tr class="page-title active-page notification-page-detail" data-notification_template_id="{{$notification_template->id}}" data-show_url="{{route('cms.notifications.show', ['id'=> $notification_template->id])}}">
                                        <td>
                                            <a class="text-body d-block" href="javascript:void(0)">{{$notification_template->label}}</a>
                                        </td>
                                    </tr>
                                @empty
                                @endforelse
                            </tbody>
                        </table>
                   </div>
                </div>
            </div>
        </div>
        <div class="col-md-7 col-xl-6 mb-2 al_cms_notification">
            <div class="card">
                <div class="card-body p-3" id="edit_page_content">
                    <div class="row">
                        <div class="col-12 text-right">
                            <button type="button" class="btn btn-info" id="update_notification_template"> {{ __("Publish") }}</button>
                        </div>
                    </div>
                    <div class="row">
                        <input type="hidden" id="notification_template_id" value="">
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-12 mb-2">
                                    <label for="title" class="control-label">{{ __("Subject") }}</label>
                                    <input class="form-control " id="subject" placeholder="Subject" name="subject" type="text">
                                    <span class="text-danger error-text updatetitleError"></span>
                                </div>
                                <div class="col-md-10 mb-2">
                                    <label for="title" class="control-label">{{ __("Content") }}</label>
                                    <textarea class="form-control" id="content" placeholder="Meta Keyword" rows="6" name="meta_keyword" cols="10" maxlength="250"></textarea>
                                </div>
                                <div class="col-md-2">
                                    <label for="title" class="control-label">{{ __("Tags") }}:-<div id="tags" disabled=""></div></label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ asset('assets/ck_editor/ckeditor.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
         $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        setTimeout(function(){
            $('tr.page-title:first').trigger('click');
        }, 500);
        $(document).on("click","#client_language",function() {
            $('tr.page-title:first').trigger('click');
        });
        $(document).on("click",".notification-page-detail",function() {
            $('#edit_page_content #content').val('');
            // $('#edit_page_content #content').summernote('destroy');
            let url = $(this).data('show_url');
            let language_id = $('#edit_page_content #client_language :selected').val();
            $.get(url,function(response) {
              if(response.status == 'Success'){
                if(response.data){
                    $('#edit_page_content #notification_template_id').val(response.data.id);
                    if(response.data){
                        $('#edit_page_content #tags').html(response.data.tags);
                        $('#edit_page_content #subject').val(response.data.subject);
                        $('#edit_page_content #content').val(response.data.content);
                        //CKEDITOR.instances.content.setData(response.data.content);
                         //$('#edit_page_content #content').summernote({'height':450});
                    }else{
                      $(':input:text').val('');
                      $('textarea').val('');
                    }
                }else{
                    $('textarea').val('');
                    $(':input:text').val('');
                    $('#edit_page_content #page_id').val('');
                }
              }
            });
        });
        $(document).on("click","#update_notification_template",function() {
            var update_url = "{{route('cms.notifications.update')}}";
            let subject = $('#edit_page_content #subject').val();
            let content = $('#edit_page_content #content').val();
           // let content = CKEDITOR.instances.content.getData();
            let email_template_id = $('#edit_page_content #notification_template_id').val();
            var data = { subject: subject, content: content, email_template_id:email_template_id};
            $.post(update_url, data, function(response) {
              $.NotificationApp.send("Success", response.message, "top-right", "#5ba035", "success");
              setTimeout(function() {
                    location.reload()
                }, 2000);
            }).fail(function(response) {
                $('#edit_page_content .updatetitleError').html(response.responseJSON.errors.edit_title[0]);
                $('#edit_page_content .updatedescrpitionError').html(response.responseJSON.errors.edit_description[0]);
            });
        });
    });
</script>
@endsection
@section('script')
{{-- <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/bootstrap.tagsinput/0.8.0/bootstrap-tagsinput.min.js"></script>
{{-- <script>
    CKEDITOR.replace('content');
    CKEDITOR.config.height = 250;
</script> --}}
@endsection
