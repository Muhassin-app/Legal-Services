<?php

namespace App\Http\Controllers\Client;

use App\Models\RentalProtection;
use Illuminate\Http\Request;
use App\Http\Controllers\Client\BaseController;
use App\Models\ClientCurrency;
use App\Models\ClientLanguage;
use Illuminate\Support\Facades\Validator;

class RentalProtectionController extends BaseController
{
    public function index(Request $request){
        $rentalProtection = RentalProtection::get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
            ->where('is_active', 1)
            ->orderBy('is_primary', 'desc')->get();
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        return view('backend.rentalProtection.index')->with(['rentalProtection' => $rentalProtection,'languages' => $langs, 'clientCurrency' => $clientCurrency]);
    }

    public function store(Request $request, $domain = '', $id = null){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'validity' => 'required',
            'price' => 'required'
        ];
        Validator::make($request->all(), $rules)->validate();
        if($id){
            $rentalProtection = RentalProtection::where('id', $id)->firstOrFail();
        }else{
            $rentalProtection = new RentalProtection();
        }
        $rentalProtection->title = $request->title;
        $rentalProtection->description = $request->description;
        $rentalProtection->price = $request->price;
        $rentalProtection->validity = $request->validity;
        $rentalProtection->save();

        return redirect(route('rental.protection'));
    }

    public function edit($domain = '', $id)
    {
        $rentalProtection = RentalProtection::where('id', $id)->firstOrFail();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'desc')->get();

        $submitUrl = route('rental.protection.store', $id);
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        $returnHTML = view('backend.rentalProtection.edit')->with(['languages' => $langs, 'rentalProtection' => $rentalProtection, 'clientCurrency' => $clientCurrency])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
    }

    public function delete($domain = '', $id){
        $rentalProtection = RentalProtection::where('id', $id)->first();
        if(empty($rentalProtection)){
            return redirect()->back()->with('error', 'Rental Protection not found!');
        }
        $rentalProtection->delete();
        return redirect()->back()->with('success', 'Rental Protection deleted successfully!');
    }
}
