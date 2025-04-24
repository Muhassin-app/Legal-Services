<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PincodeDeliveryOption extends Model
{
    use HasFactory;
    protected $fillable = ['pincode_id', 'delivery_option_type'];

    protected $appends = ['delivery_option_type_text'];

    public static $delivery_option_type = [
        'same_day_delivery' => 1,
        'next_day_delivery' => 2,
        'hyper_local_delivery' => 3,
    ];

    public static $delivery_option_type_text = [
        1 => 'Same day delivery',
        2 => 'Next day delivery',
        3 => 'Hyper local delivery',
    ];

    public function getDeliveryOptionTypeTextAttribute(){
        if($this->delivery_option_type){
            return self::$delivery_option_type_text[$this->delivery_option_type];
        }else{
            return NULL;
        }
    }
}
