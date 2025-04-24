<div class="row">
   <input type="hidden" name="id" value="{{$id}}" />
    @if(@$influencer_tier)
    <div class="col-sm-12 mb-2">
        <label for="tier">Tier</label>
        <select class="selectize-select form-control" id="tierdata" name="tier" required>
            <option value="">--Select Tier--</option>

            @foreach($influencer_tier as $tier)
            <option value="{{$tier->id}}" >{{$tier->name}}</option>
            @endforeach
        </select>
    </div>
    @endif
   
</div>