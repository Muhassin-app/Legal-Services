<form id="updatePermissionForm" class="role_table_per">
    <input type="hidden" value="" name="role_id">
    <input type="hidden" value="update_role" name="action">
    <div class="table-responsive">
        <table class="table custom-table module-role">
            <thead>
                <tr>
                    <th class="fw-bolder">Module Permission</th>
                    <th class="text-center fw-bolder"></th>
                    <th class="text-center fw-bolder"></th>
                    <th class="text-center fw-bolder"></th>
                    <th class="text-center fw-bolder"></th>
                </tr>
            </thead>
            <tbody>
                @php
                    //pr($prmArr);
                    $last_conter = 0; @endphp
                    @foreach($prmArr as $key => $perm)
                        <tr>
                            @php 
                                $last = 4;
                                $pcount = count($perm);
                                $ch = 0;
                            @endphp
                            <td>
                                <i class="ti-folder"></i> 
                                <input type="hidden" value="" name="" ><span>{{$key}}</span>
                            </td>   
                            @php $last_con = count($perm);  $last_conter = 0;
                                $i= 0;
                                $mo = ($last_con%$last);
                                if($mo > 0 && $last_con>0){
                                    $mo = $last - $mo;
                                    for(;$i<$mo;$i++){
                                        $perm[] = array_merge($perm,['name' => 'xoxo']);
                                    }
                                }
                            @endphp
                            @foreach($perm as $key2 => $permData)
                                @if($permData['name'] != 'xoxo')
                                    <td class="text-left">
                                        <input class="access_module" type="checkbox"  name="permission_arr[]" value="{{$permData['id']}}" {{ in_array($permData['id'], $role_has_permission_ids) ? 'checked' : '' }}>
                                        <span class="">{{$permData['name']}}</span>
                                    </td>
                                @else  
                                    <td class="text-left"> <input type="hidden" value="" name="" ><span class=""></span></td>
                                @endif
                                @php    
                                    $mod = ($last_conter+1)%$last;
                                @endphp
                            @if($mod == 0 && $last_con != $last_conter+1)
                        </tr>
                        @if($permData['name'] != 'xoxo')
                            <tr>
                                <td>
                                    <input type="hidden" value="" name="" ><span></span>
                                </td>
                            @endif
                        @endif
                        @php $last_conter++; @endphp
                        @endforeach    
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </form>