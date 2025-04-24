
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/jquery.datetimepicker.min.css" />
<div class="alRentalSinglePageView">
  <div class="product-end-date">
    <div class="form-group">
      <label for="exampleInputEmail1">Product End Date</label>
      <input type="text" class="form-control" id="end_date" aria-describedby="emailHelp" placeholder="Product End Date" value="{{$request['vendor_end_date_time']??''}}" readonly>
    </div>
  </div>
  
  <div class="product-end-date">
    <div class="form-group">
      <label for="extendedDate">Extended Date</label>
      <input type="text" class="form-control" id="datetimepicker" aria-describedby="extendedDate" placeholder="Product End Date" value="">
    </div>
  </div>
  
  <div class="product-end-date">
    <div class="form-group">
      <label for="extendedAmount">Extended Amount</label>
      <input type="text" class="form-control" id="extended_amount" name="extended_amount" placeholder="Extended Amount" value="" readonly>
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-datetimepicker/2.5.4/build/jquery.datetimepicker.full.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {
    // $.datetimepicker.setLocale('pt-BR');
    $('#datetimepicker').datetimepicker({
      step: 1,
      format:'Y-m-d H:m:s',
      minDate: "{{$request['vendor_end_date_time']??''}}"
    });

  });

  $(document).on("change","#datetimepicker" ,() => {
    var product_end_date = "{{$request['vendor_end_date_time']??''}}";
    var product_extended_date = $('#datetimepicker').val();
    var product_incremental_price = "{{$product->variant[0]->incremental_price}}";
    
  });
</script>