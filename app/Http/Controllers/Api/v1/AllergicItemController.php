<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\AllergicItem;
use App\Models\User;
use App\Models\UserAllergicItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AllergicItemController extends Controller
{
    public function index(Request $request)
    {
        $data['allergic_items'] = AllergicItem::get(); 
        return response()->json(['status' => 200, 'message' => 'List of allergic items', 'data' => $data]);
    }

    public function userAllergicItems(Request $request)
    {
        $data['items'] = AllergicItem::wherehas('users',function($q){
            $q->where('user_id', Auth::id());
        })->get();

        $data['custom_allergic_items'] = auth()->user()->custom_allergic_items;
        
        return response()->json(['status' => 200, 'message' => 'List of user allergic items','data' => $data]);
    }

    public function addUpdateAllergicItems(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'allergic_item_ids' => 'required|array',
        ]);
        
        if ($validator->fails()) {
            return response()->json(['status' => 201,'message' => $validator->errors()->first()], 201);
        }
        
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

        return response()->json(['status' => 200, 'message' => 'Update your items']);
    }

    public function destroy($id)
    {
        $doesntExist = UserAllergicItem::where(['user_id' => Auth::id(), 'allergic_item_id' => $id])->doesntExist();
        if ($doesntExist) {
            return response()->json(['status' => 201, 'message' => 'Item is not in your list']);
        }
        UserAllergicItem::where(['user_id' => Auth::id(), 'allergic_item_id' => $id])->delete();
        return response()->json(['status' => 200, 'message' => 'delete the item']);
    }
}
