<?php

namespace App\Http\Controllers\Client;

use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{VendorSlotDate, Vendor, VendorSlot, SlotDay, ServiceArea, ClientPreference, Company, Language, Currency};
use Session;

class CompanyController extends BaseController{

    use ApiResponser;

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request, $domain = ''){
        $client_preferences = ClientPreference::first();
        $company = Company::orderBy('id','desc')->get();

        $returnData['company'] = $company;
        return view('backend/company/index')->with($returnData);
    }


    public function store(Request $request, $domain = ''){
        $rules = array(
            'name' => 'required',
            'email' => 'required|email|unique:companies,email',
            'phone_number' => 'required|numeric|unique:companies,phone_number'
        );
        $messages = array(
            'name.required' => 'Area name is required',
            'email.required' => 'Email is required and Unique',
        );
        $validation  = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $area = new Company();
        $area->name             = $request->name;
        $area->email        = $request->email;
        $area->address       = $request->address;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file_name = 'Clientlogo/' . uniqid() . '.' .  $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->put($file_name, file_get_contents($file), 'public');
            $area->logo = $file_name;
        }

        // $area->logo       = $request->logo;
        $area->phone_number      = $request->phone_number;
        $area->save();

        return redirect()->back()->with('success', 'Company saved successfully!');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $domain = ''){
        $area = Company::where('id', $request->data)->first();
        // dd($area->logo);
        $returnHTML = view('backend.company.editArea')->with(['area' => $area])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $domain = '', $id){
        $rules = array(
            'name' => 'required',
            'email' => 'required|email|unique:companies,email,'.$id,
            'phone_number' => 'required|numeric|unique:companies,phone_number,'.$id
        );
        $messages = array(
            'name.required' => 'Area name is required',
            'email.required' => 'Email is required and unique',
        );
        $validation  = Validator::make($request->all(), $rules, $messages);

        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        }
        $area = Company::where('id', $id)->firstOrFail();
        $area->name             = $request->name;
        $area->email            = $request->email;
        $area->address          = $request->address;

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $file_name = 'Clientlogo/' . uniqid() . '.' .  $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->put($file_name, file_get_contents($file), 'public');
            $area->logo = $file_name;
        }

        $area->phone_number      = $request->phone_number;
        $area->save();
        return redirect()->back()->with('success', 'Company updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\ServiceArea  $serviceArea
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $domain = ''){
        $area = Company::where('id', $request->area_id)->delete();
        return redirect()->back()->with('success', 'Company deleted successfully!');
    }

   
}
