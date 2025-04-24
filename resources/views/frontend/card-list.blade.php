     <div class="row w-100">

        @foreach($cards as $k => $card)

        <div class="col-md-6 mb-2">
            <div class="delivery_box cart_delivery p-2 mb-sm-3 mb-1 position-relative">
                <label class="radio m-0">{{$card->card_hint}}, {{$card->brand}}
                    @if($card->is_default)
                    <input type="radio" name="azul_card_id" value="{{$card->id}}" checked="checked">
                    @else
                    <input type="radio" name="azul_card_id" value="{{$card->id}}" {{$k == 0? 'checked="checked"' : '' }}>
                    @endif
                    <span class="checkround"></span>
                </label>
            </div>
        </div>
@endforeach
        </div>