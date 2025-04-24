<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Front\AzulPaymentController;

class UserDataVault extends Model
{
    use HasFactory;

    protected $table = 'user_data_vault';

    protected $fillable = [
        'user_id',
        'token',
        'is_default',
        'expiration',
        'brand',
        'card_hint'
    ];
    public function getExpirationAttribute()
    {
        return substr_replace($this->attributes['expiration'],"/",4,0);
    }
    
    public static function defaultCard($id){
        $user = Auth::user();
        $card = UserDataVault::where('id', $id)->first();
        if($card){
            UserDataVault::where('user_id', $user->id)->update(['is_default' => 0]);
            $card->is_default =1;
            $card->save();
            return true;
        }
        return false;
    }
    
    public static function deleteCard($id){
        $card = UserDataVault::where('id', $id)->first();
        if($card){
            $azul = new AzulPaymentController();
            $azul->deleteDatavault($card);
            return true;
        }
        return false;
    }
}
