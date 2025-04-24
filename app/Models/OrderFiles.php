<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
class OrderFiles extends Model
{
    use HasFactory;
    public static function SaveFiles($request =[])
    {
     
        foreach ($request->instructions_files as $instructions_file) {
          
            $file_url = Storage::disk('s3')->put('orderFile', $instructions_file,'public');
            $OrderFile = new OrderFiles();
            $OrderFile->cart_id =$request->cart_id;
            $OrderFile->file =$file_url;
            $OrderFile->save();
        }
        $orderFiles =  OrderFiles::where('cart_id',$request->cart_id)->get();
        return $orderFiles;
    }
    public function getFileAttribute($value)
    {
      $values = array();
      $img = 'default/default_image.png';
      if(!empty($value)){
        $img = $value;
      }
      $ex = checkImageExtension($img);
      $values['proxy_url'] = \Config::get('app.IMG_URL1');
      $values['image_path'] = \Config::get('app.IMG_URL2').'/'.Storage::disk('s3')->url($img).$ex;
      $values['image_fit'] = \Config::get('app.FIT_URl');
      $values['original'] = $value;

      return $values;
    }
}
