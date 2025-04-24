<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserVerificationResource extends Model
{
    use HasFactory;
    protected $fillable = ['user_verification_id', 'type', 'datapoints'];
    protected $appends = ['resources'];

    public function getResourcesAttribute($value){
        return json_decode($this->datapoints);
    }

    public function addResource($data)
    {
    	return self::updateOrCreate([
    		'user_verification_id' => $data['user_verification_id'],
    		'type' => $data['type'],
    	],[
    		'datapoints' => $data['datapoints']
    	]);
    }
}
