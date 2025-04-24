<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = ['title','type','sms_text','email_title','email_subject','email_body','push_title', 'push_image', 'push_message_body', 'push_url_option','push_url_option_value','send_to','schedule_datetime','request_user_count','request_time_difference','total_request_count','status'];

    public function getPushImageAttribute($value)
    {
      $values = array();
      $img = '';
      if(!empty($value)){
        $img = $value;

        $ex = checkImageExtension($img);
        $values['proxy_url'] = \Config::get('app.IMG_URL1');
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.\Storage::disk('s3')->url($img).$ex;
        $values['image_fit'] = \Config::get('app.FIT_URl');
      }else{
        $values['proxy_url'] = '';
        $values['image_path'] = '';
        $values['image_fit'] = '';
      }
     
      return $values;
    }

}
