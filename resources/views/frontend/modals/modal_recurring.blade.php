
<div class="modal fade recurring-modal" role="dialog"  aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h4 class="modal-title"> {{getNomenclatureName('Recurring', true)}} Details</h4>
          <button type="button" class="close" data-dismiss="modal" aria- label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body"> 
          <div class="m-2">
              <h4>{{getNomenclatureName('Recurring', true)}} Days : <span id="days-recurring"></span></h4>
              <h4>{{getNomenclatureName('Recurring', true)}} Slot : <span id="slot-recurring"></span></h4>
              <h4>{{getNomenclatureName('Recurring', true)}} Date :
              <span id="date-recurring"></span></h4>
          </div>  
        </div>
      </div>
    </div>
  </div>
