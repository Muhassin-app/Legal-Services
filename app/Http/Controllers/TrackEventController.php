<?php

namespace App\Http\Controllers;

use App\Models\TrackEvent;
use App\Models\User;
use Illuminate\Http\Request;

class TrackEventController extends Controller
{
    //


    public function saveEvents(Request $request)
    {
        $data = array(
            'location' => $request->location,
            'details' => 'User-Id : '.auth()->id().', Name : '.auth()->user()->name.', Date : '.date('d-m-Y H:i:a').', '.json_encode($request->all())
        );
        TrackEvent::create($data);
    }
}
