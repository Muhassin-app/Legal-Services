<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderStatusOption extends Model
{
    use HasFactory;
    
    public static function findNext($id){
	    return static::where('id', '>', $id)->first();
	}
    public function getPreference(){
      return $this->hasOne('App\Models\ClientPreference','client_code','code');
    }

       // do not use this. price based on role
       public function getStatusName($luxury)
       {

        foreach(config('constants.VendorTypesLuxuryOptions') as $ids)
        {
          // return $this->id;
          //$luxury option id is  == 5 for pick and drop (taxi flow) we change status delivered to Completed
          if(($ids == $luxury) && $luxury == '5' && $this->id == 6){
              return 'Completed';
          }
        }
           return $this->title;
       }

}
