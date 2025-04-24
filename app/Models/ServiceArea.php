<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceArea extends Model
{
    protected $appends = ['geo_coordinates'];

    protected $fillable = ['name','description','geo_array','zoom_level','polygon','vendor_id', 'is_active_for_vendor_slot', 'area_type', 'primary_language', 'primary_currency'];

    public function getGeoCoordinatesAttribute(){
        $data = [];  
        $temp = $this->geo_array;
        $temp = str_replace('(','[',$temp);
        $temp = str_replace(')',']',$temp);
        $temp = '['.$temp.']';
        $temp_array =  json_decode($temp,true);
        
        if(is_array($temp_array))
        {
            foreach($temp_array as $k=>$v){
                $data[] = [
                    'lat' => $v[0] ?? '',
                    'lng' => $v[1] ?? ''
                ];
            }
        }
       
        return $data;
    }
}
