<!-- Modal -->
<div class="modal fade" id="consent_form_rental" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="exampleModalLongTitle">{!!@$page->translation->title!!}</h3>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                {!!@$page->translation->description!!}
            </div>
            <div class="modal-footer">
                <button type="button" id="agree_btn" class="btn btn-primary">Agree</button>
            </div>
        </div>
    </div>
</div>