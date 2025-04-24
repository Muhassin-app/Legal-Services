
 <?php
 $existSet = array();

 $mediaPath = Storage::disk('s3')->url('default/default_image.png');

 if (!empty($varnt->vimage) && isset($varnt->vimage->pimage->image)) {
     $mediaPath = $varnt->vimage->pimage->image->path['proxy_url'] . '100/100' . $varnt->vimage->pimage->image->path['image_path'];
 }
 $existSet = explode('-', $varnt->sku);
 $vsets = '';
 
 foreach ($varnt->set as $vs) {
     if(isset($vs) && !empty($vs->title)){
         $vsets .= $vs->title . ', ';
     }


 }
 ?>
<tr id="tr_{{$varnt->id}}">
    <td>
        <div class="image-upload">
            <label class="file-input uploadImages" for="{{$varnt->id}}">
                <img src="{{$mediaPath}}" width="30" height="30" for="{{$varnt->id}}" />
            </label>
        </div>
        <div class="imageCountDiv{{$varnt->id}}"></div>
    </td>
    <td>
        <input type="hidden" name="variant_ids[]" value="{{$varnt->id}}">
        <input type="hidden" class="exist_sets" value="{{$existSet[(count($existSet) - 1)]}}">
        <input type="text" name="variant_titles[]" value="{{$varnt->title??null}}">
    </td>
    
        @if($productVariants->count())
            @if(@$show)
                @php $product_id = $product_id; @endphp
            @else
                @php $product_id = $product->id; @endphp
            @endif
            <td>
                @foreach($productVariants as $vk => $var)
                {{-- <div class="col-sm-3">
                    <label class="control-label">{{$var->title??null}}</label>
                </div> --}}
            
                <div class="col-sm-9">
                    <select class="variant_sets">  
                        <option data-product_id="{{$product_id}}" data-p_variant_id="{{$varnt->id}}" data-varId="{{$var->id}}" value="">Select</option>
                        @foreach($var->option as $key2 => $opt)
                            @if(isset($opt) && !empty($opt->title) && isset($var) && !empty($var->title) )
                                <div class="checkbox checkbox-success form-check-inline pr-3">
                                    <?php //print_r(@$existOptions[$key]);print_r($key);print_r($opt->id)  ?>
                                    <option data-product_id="{{$product_id}}" data-p_variant_id="{{$varnt->id}}" data-opt="{{$opt->id}}" data-varId="{{$var->id}}" value="{{$opt->id}}" <?php echo ($opt->id == @$existOptions[$key]) ? "selected" : '' ?> >{{$opt->title}}</option>
                                    {{-- <input type="checkbox" name="variant{{$var->id}}" class="intpCheck" opt="{{$opt->id.';'.$opt->title}}" varId="{{$var->id.';'.$var->title}}" id="opt_vid_{{$opt->id}}" @if(in_array($opt->id, $existOptions)) checked @endif>
                                    <label for="opt_vid_{{$opt->id}}">{{$opt->title}}</label> --}}
                                </div>
                            @endif
                        @endforeach
                    </select>
                </div>
                @endforeach
            </td>
        @endif
    {{-- <td>{{rtrim($vsets, ', ')}}</td> --}}
    <td>
        <input type="text" style="width: 70px;" name="variant_price[]" value="{{decimal_format($varnt->price)}}" onkeypress="return isNumberKey(event)">
    </td>
    <td>
        <input type="text" style="width: 100px;" name="variant_minimum_duration[]" value="{{decimal_format($varnt->minimum_duration)}}" onkeypress="return isNumberKey(event)">
    </td>
    <td>
        <input type="text" style="width: 70px;" name="variant_incremental_price[]" value="{{decimal_format($varnt->incremental_price)}}" onkeypress="return isNumberKey(event)">
    </td>
    {{-- <td class="check_inventory">
        <input type="text" style="width: 70px;" name="variant_quantity[]" value="{{$varnt->quantity}}" onkeypress="return isNumberKey(event)">
    </td> --}}
    <td>
        
        <a href="javascript:void(0);" data-varient_id="{{$varnt->id}}" class="action-icon deleteExistRowRental">
            <i class="mdi mdi-delete"></i>
        </a>
    
        @if(@$show)
            <a href="javascript:void(0);" data-varient_id="{{$varnt->id}}" data-product_id="{{$product_id}}" data-variant_title="{{$varnt->title}}" class="action-icon getScheduledTable">
                <i class="mdi mdi-eye"></i>
            </a>
            <a href="javascript:void(0);" data-category_id="{{$product_category_id}}" data-varient_id="{{$varnt->id}}"  data-product_id="{{$product_id}}" class="action-icon product_varient_ids addExistRow">
                <i class="mdi mdi-plus"></i>
            </a>
        @else
            {{-- @if (@$key == $variant_count)
               
            @endif --}}
            <a href="javascript:void(0);" data-varient_id="{{$varnt->id}}" data-product_id="{{$product->id}}" data-variant_title="{{$varnt->title}}" class="action-icon getScheduledTable">
                <i class="mdi mdi-eye"></i>
            </a>
            <a href="javascript:void(0);" data-category_id="{{$product->category_id}}" style="display: <?php echo (@$key == $variant_count) ? 'inline-block' : 'none'; ?>" data-varient_id="{{$varnt->id}}"  data-product_id="{{$product->id}}" class="action-icon product_varient_ids addExistRow">
                <i class="mdi mdi-plus"></i>
            </a>
        @endif

      
    </td>
</tr>