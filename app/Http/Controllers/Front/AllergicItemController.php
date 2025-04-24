<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\AllergicItem;
use App\Models\User;
use App\Models\UserAllergicItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AllergicItemController extends Controller
{
    public function index(Request $request)
    {
        $data['items'] = AllergicItem::wherehas('users',function($q){
            $q->where('user_id', Auth::id());
        })->get();
        $data['selected_ids'] = AllergicItem::wherehas('users',function($q){
            $q->where('user_id', Auth::id());
        })->pluck('id')->toArray();

        $data['allergic_items'] = AllergicItem::get(); 
        return view('frontend.account.allergic-items',$data);
    }

    public function addUpdateAllergicItems(Request $request)
    {
        foreach($request->allergic_item_ids as $id){
            UserAllergicItem::updateOrCreate([
                'user_id' => Auth::id(),
                'allergic_item_id' => $id,
            ],[
                'user_id' => Auth::id(),
                'allergic_item_id' => $id,
            ]);
        }

        UserAllergicItem::where('user_id', Auth::id())->whereNotIn('allergic_item_id',$request->allergic_item_ids)->delete();

        User::where('id',Auth::id())->first()->update([
            'custom_allergic_items' => $request->custom_allergic_items
        ]);
        return back();
    }

    public function destroy($domain, $id)
    {
        UserAllergicItem::where(['user_id' => Auth::id(), 'allergic_item_id' => $id])->delete();
        return back();
    }
}
