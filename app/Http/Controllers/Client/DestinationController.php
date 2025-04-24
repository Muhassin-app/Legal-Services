<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ClientCurrency;
use App\Models\ClientLanguage;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class DestinationController extends Controller
{
    public function index(Request $request){
        $destinations = Destination::get();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
            ->where('is_active', 1)
            ->orderBy('is_primary', 'desc')->get();
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        return view('backend.destination.index')->with(['destination' => $destinations,'languages' => $langs, 'clientCurrency' => $clientCurrency]);
    }

    public function store(Request $request, $domain = '', $id = null){
        $rules = [
            'title' => 'required',
            'image' => 'required'
        ];
        Validator::make($request->all(), $rules)->validate();
        if($id){
            $destination = Destination::where('id', $id)->firstOrFail();
        }else{
            $destination = new Destination();
        }
        $destination->title = $request->title;
        $destination->address = $request->address;
        $destination->latitude = $request->latitude;
        $destination->longitude = $request->longitude;
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $destination->image = Storage::disk('s3')->put('/destination', $file, 'public');
        }
        $destination->save();

        return redirect(route('destinations'));
    }

    public function edit($domain = '', $id)
    {
        $destination = Destination::where('id', $id)->firstOrFail();
        $langs = ClientLanguage::with('language')->select('language_id', 'is_primary', 'is_active')
                    ->where('is_active', 1)
                    ->orderBy('is_primary', 'desc')->get();

        $submitUrl = route('destination.store', $id);
        $clientCurrency = ClientCurrency::select('currency_id')->where('is_primary', 1)->with('currency')->first();
        $returnHTML = view('backend.destination.edit')->with(['languages' => $langs, 'destination' => $destination, 'clientCurrency' => $clientCurrency])->render();
        return response()->json(array('success' => true, 'html'=>$returnHTML, 'submitUrl' => $submitUrl));
    }

    public function delete($domain = '', $id){
        $destination = Destination::where('id', $id)->first();
        if(empty($destination)){
            return redirect()->back()->with('error', 'Destination not found!');
        }
        $destination->delete();
        return redirect()->back()->with('success', 'Destination deleted successfully!');
    }
}