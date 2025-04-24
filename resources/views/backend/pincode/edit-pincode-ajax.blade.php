<div class="row">
    
    <div class="col-12">
        <input type="hidden" name="pincode_id" id="pincode_id" value="{{$pincode->id}}">
        <input type="hidden" name="vendor_id" id="vendor_id" value="{{$pincode->vendor_id}}">
        <div class="form-group">
            <label for="title">Pincode</label>
            <input type="number" oninput="javascript: if (this.value.length > this.maxLength) this.value = this.value.slice(0, this.maxLength);" maxlength = "6" class="form-control" name="pincode" id="pincode" placeholder="Enter Pincode" value="{{$pincode->pincode}}" required>
        </div>
        @php
            $delivery_option_ids = $pincode->deliveryOptions->pluck('delivery_option_type')->toArray();
        @endphp
        <div class="form-group">
            <label for="title">Select Delivery Option</label>
            <select class="selectizeInput form-control" id="select_delivery_option" name="delivery_option_ids[]" placeholder="Select Delivery Option" multiple required>
                <option value="1" @if(in_array(1, $delivery_option_ids)) selected @endif>Same Day Delivery</option>
                <option value="2" @if(in_array(2, $delivery_option_ids)) selected @endif>Next Day Delivery</option>
                <option value="3" @if(in_array(3, $delivery_option_ids)) selected @endif>Hyper Local Delivery</option>
            </select>
        </div>
    </div>
</div>