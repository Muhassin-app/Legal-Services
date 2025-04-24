<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
class CarImages extends Model
{
    use HasFactory;
    public static function saveImage($request){
        if ($request->hasFile('image')) {
            $filePath ='car_imges' . '/' . Str::random(40);
            $file = $request->file('image');
            $file_name = Storage::disk('s3')->put($filePath, $file, 'public');

            $newCarImage = new CarImages();
            $newCarImage->image =  $file_name;
            $newCarImage->car_id =  $request->car_id;
            $newCarImage->save();
            return $newCarImage;
        }
    }

    public function getImageAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $img = str_replace(' ', '', $img);
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      if (substr($img, 0, 7) == "http://" || substr($img, 0, 8) == "https://"){
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.$img;
      } else {
        $values['image_path'] = \Config::get('app.IMG_URL2').'/'.Storage::disk('s3')->url($img).$ex;
      }
      $values['image_fit'] = \Config::get('app.FIT_URl');
      return $values;
    }
}
