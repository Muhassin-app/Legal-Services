<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DownloadFileController extends Controller
{
    public function index(Request $request, $domain = '', $file_name){

		if(session()->get("applocale_admin") == "ta"){
		    $file_path = public_path($file_name);	
		}elseif(session()->get("applocale_admin") == "ar"){
			$file_path = public_path($file_name);
		}elseif(session()->get("applocale_admin") == "fr"){	
			$file_path = public_path($file_name);
		}elseif(session()->get("applocale_admin") == "de"){
			$file_path = public_path($file_name);
		}else{
			$file_path = public_path($file_name);
		}
		    	
    	$headers = ['Content-Type: application/csv'];
    	return response()->download($file_path, $file_name, $headers);
    }
}
