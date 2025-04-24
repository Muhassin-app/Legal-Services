<div class="modal fade remove-cart-modal" id="remove_cart_modal" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="remove_cartLabel" style="background-color: rgba(0,0,0,0.8);">
    <div class="modal-dialog modal-dialog-centered">
       <div class="modal-content">
          <div class="modal-header pb-0">
             <h5 class="modal-title" id="remove_cartLabel">{{__('Remove Cart')}}</h5>
             <button type="button" class="close" data-dismiss="modal" aria-label="Close"> <span aria-hidden="true">×</span> </button>
          </div>
          <div class="modal-body text-center">
             <h6 class="m-0 px-3">{{__('This change will remove all your cart products. Do you really want to continue ?')}}</h6>
          </div>
          <div class="modal-footer flex-nowrap justify-content-center align-items-center"> <button type="button" class="btn btn-solid black-btn" data-dismiss="modal">{{__('Cancel')}}</button> <button type="button" class="btn btn-solid" id="remove_cart_button" data-cart_id="" data-ondemand_vendor_type="">{{__('Remove')}}</button> </div>
       </div>
    </div>
 </div>
 