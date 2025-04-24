<?php

namespace App\Http\Controllers\Client;
use DB;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Models\ClientSlot;
use App\Http\Controllers\Client\BaseController;

class ClientSlotController extends BaseController{
    use ApiResponser;


    public function store(Request $request){
        try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
               ],['name' => 'The name field is required.']);
            DB::beginTransaction();

            $slot = new ClientSlot();
            $slot->name = $request->name;
            $slot->start_time = $request->start_time;
            $slot->end_time = $request->end_time;
            $slot->save();
         
            DB::commit();
            return $this->successResponse($slot, 'Slot Added Successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request){
        try {
            $tag = ClientSlot::where(['id' => $request->slot_id])->first();
            return $this->successResponse($tag, '');
        } catch (\Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request){
         try {
            $this->validate($request, [
                'name' => 'required|string|max:255',
                'start_time' => 'required|string',
                'end_time' => 'required|string',
               ],['name' => 'The name field is required.']);
            DB::beginTransaction();
            $slot_id = $request->slot_id;
            $slot = ClientSlot::where('id', $slot_id)->first();
            $slot->name = $request->name;
            $slot->start_time = $request->start_time;
            $slot->end_time = $request->end_time;
            $slot->save();
        
            DB::commit();
            return $this->successResponse($slot, 'Slot Updated Successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return $this->errorResponse([], $e->getMessage());
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Item  $item
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request){
        try {
            ClientSlot::where('id', $request->slot_id)->delete();
            return $this->successResponse([], 'Slot Deleted Successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse([], $e->getMessage());
        }
    }
}