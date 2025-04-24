<link rel="stylesheet" href="https://cdn.datatables.net/1.12.1/css/jquery.dataTables.min.css">
<div class="modal fade inventoryModal" id="inventoryModal" role="dialog" aria-labelledby="inventoryModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title" id="inventoryModalLabel"> View Inventory</h4>
        <button type="button" class="close" data-dismiss="modal" aria- label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <div id="modalContent">
          <a href="http://127.0.0.1:9000/admin/invenory_login" target="_blank" id="inventoryLogin"> inventory login </a>
        </div>
      </div>
      <div class="modal-footer">
        <button style="width: auto" type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        {{-- <button type="button" class="btn btn-primary">Save changes</button> --}}
      </div>
    </div>
  </div>
</div>
<?php $preference =  App\Services\InventoryService::checkIfInventoryOn();
if(empty($preference)){
  $preference = 'null';
}
?>
<script>
 var preference = <?php echo $preference; ?>;
  $(document).on('click', '#inventoryModalShow', function() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('input[name="_token"]').val()
        }
    });
    $.ajax({
      url: "/client/getInvetoryToken",
      type: "GET",
      data: {
        email: ""
      },
      success: function(response) {
        if(response.success){
          $('#inventoryLogin').attr('href', preference.inventory_service_key_url+"/admin/invenory_login/"+response.data);
          $('#inventoryModal').modal('show');
        }
      },
    });
  })
</script>