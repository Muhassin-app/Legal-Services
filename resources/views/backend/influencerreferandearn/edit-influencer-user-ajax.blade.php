<input type="hidden" name="influencer_user_id" value="{{$influencer_users->id??''}}">
<div class="row">
    <div class="col-sm-12 mb-2">
        <label for="title" class="control-label">Commision Type</label>
        <select class="selectize-select form-control" name="commision_type" required="">
            <option value="1" {{ ( $influencer_users->commision_type == 1) ? 'selected' : '' }}>Percentage</option>
            <option value="2" {{ ( $influencer_users->commision_type == 2) ? 'selected' : '' }}>Fixed</option>
        </select>
    </div>
    <div class="col-sm-12 mb-2">
        <label for="title" class="control-label">Commision</label>
        <input class="form-control" min="1" onkeypress="return isNumberKey(event)" placeholder="Commision" required="required" name="commision" type="number" value="{{$influencer_users->commision??''}}">
    </div>
</div>