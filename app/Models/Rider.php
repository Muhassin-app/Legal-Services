<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rider extends Model
{
    use HasFactory;
    protected $fillable = ['user_id','first_name','last_name','dial_code','phone_number','email'];

    public function createRider($data)
    {
    	return self::updateOrCreate([
    		'phone_number' => $data['phone_number']
    	],[
            'dial_code' => $data['dial_code']??'',
    		'user_id' => $data['user_id'],
    		'first_name' => $data['first_name'],
    		'last_name' => $data['last_name'],
    		'email' => $data['email']??null
    	]);
    }
    public function deleteRider($id)
    {
    	return self::where('id',$id)->delete();
    }
    public function getAllByUserId($user_id)
    {
    	return self::where('user_id',$user_id)->orderBy('id', 'DESC')->get();
    }
    public function getCount($user_id)
    {
    	return self::where('user_id',$user_id)->orderBy('id', 'DESC')->count();
    }
}
