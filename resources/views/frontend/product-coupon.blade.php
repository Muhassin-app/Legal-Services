@if( !empty($coupon_list) )
    <div class="col-md-3">
        <div class="aside_bar">
            <h5>Available offers</h5>
                <div class="discriptions">
                    @foreach($coupon_list as $m_key => $m_val)
                    <p> <small> Coupon Code :  </small><span>{{ $m_val['name'] ?? '' }} </span> </p>
                        <p> <small> Description :</small> <span>{{ $m_val['short_desc'] ?? '' }} </span> </p>
                        
                        <p>  <small>Coupon Type :  </small><span>{{ $m_val['promo_type_title'] ?? '' }} </span> </p>
                        <p> 
                        <small>
                        @if($m_val['promo_type_id'] == 1)
                                Amount : 
                        @else
                            Percentage : 
                        @endif
                        </small>
                        <span>{{decimal_format($m_val['amount'])}}</span>
                        </p>
                        <hr>
                    @endforeach
                </div>
            
            <!-- <form>
                <div class="form-group">
                    <input type="text" class="form-control" id="" aria-describedby="emailHelp" placeholder="Enter Promo Code">
                    <button type="submit" class="btn btn-primary">Apply</button>
                </div>
            </form> -->
        </div>
    </div>
@endif