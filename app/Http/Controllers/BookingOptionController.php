<?php

namespace App\Http\Controllers;

use App\Models\BookingOption;
use App\Models\ClientCurrency;
use App\Models\ClientLanguage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingOptionController extends Controller
{
    public function index(Request $request){
        $bookingOption = BookingOption::get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
            ->where('is_active', 1)
            ->orderBy('is_primary', 'desc')->get();
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        return view('backend.bookingOption.index')->with(['bookingOption' => $bookingOption,'languages' => $langs, 'clientCurrency' => $clientCurrency]);
    }

    public function store(Request $request, $domain = '', $id = null){
        $rules = [
            'title' => 'required',
            'description' => 'required',
            'price' => 'required'
        ];
        Validator::make($request->all(), $rules)->validate();
        if($id){
            $bookingOption = BookingOption::where('id', $id)->firstOrFail();
        }else{
            $bookingOption = new BookingOption();
        }
        $bookingOption->title = $request->title;
        $bookingOption->description = $request->description;
        $bookingOption->price = $request->price;
        $bookingOption->save();

        return redirect()->back()->with('success', 'Booking Option created successfully!');
    }

    public function edit($domain = '', $id)
    {
        $bookingOption = BookingOption::where('id', $id)->firstOrFail();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'desc')->get();

        $submitUrl = route('booking.option.store', $id);
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        $returnHTML = view('backend.bookingOption.edit')->with(['languages' => $langs, 'bookingOption' => $bookingOption, 'clientCurrency' => $clientCurrency])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
    }

    public function delete($domain = '', $id){
        $bookingOption = BookingOption::where('id', $id)->first();
        if(empty($bookingOption)){
            return redirect()->back()->with('error', 'Booking Option not found!');
        }
        $bookingOption->delete();
        return redirect()->back()->with('success', 'Booking Option deleted successfully!');
    }
}
