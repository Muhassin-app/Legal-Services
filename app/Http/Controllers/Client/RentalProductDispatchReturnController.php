<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\OrderProductDispatchReturnRoute;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RentalProductDispatchReturnController extends Controller
{
    
    public function index(Request $request)
    {
        $user = Auth::user();
        $return_form_requests = OrderProductDispatchReturnRoute::with(['order', 'orderProduct', 'orderProduct.pvariant'])->where('dispatcher_status_option_id', 4)->paginate(10);

        // all vendors
        $vendors = Vendor::where('status', '!=', '2')->orderBy('id', 'desc');
        if ($user->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendors = $vendors->get();
        
        return view('backend.rental_return_requests.index', compact('return_form_requests', 'vendors'));
    }
}
