<?php

namespace App\Http\Controllers\Front;

use App\Helpers\Payment;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Traits\OrderTrait;
use App\Models\ClientCurrency;
use App\Models\Company;
use App\Models\Order;
use App\Models\PaymentOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use PDF;

class PayCompanyController extends Controller
{

    use ApiResponser,OrderTrait;

 
    public function __construct()
    {
       $payOpt = PaymentOption::select('credentials', 'test_mode','status')->where('code', 'payViaCompany')->where('status', 1)->first();
    }


    public function orderNumber($request)
    {
        $time = time();
        $user_id = auth()->id();
        $amount = $request->amt??$request->amount;
        if ($request->payment_from == 'pickup_delivery') {
            $time = $request->order_id??$request->order_number;
            Payment::create([
                'amount' => 0,
                'transaction_id' => $time,
                'balance_transaction' => $amount,
                'type' => 'pickup_delivery',
                'date' => date('Y-m-d'),
                'user_id' => $user_id,
                'payment_from'=>$request->user_from??'web'
            ]);
        }
        return $time;
    }


    public function payByCompany(Request $request)
    {   
        $company = Company::where('id',auth()->user()->company_id)->first();
        $order = Order::where('order_number',$request->order_number)->first();
        $data["email"] = $company->email??null;
        $data["title"] = "Its Testing email";
        $data["body"] = "This is Body part";

        $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
    
        
         view()->share('clientCurrency',$clientCurrency); 
         view()->share('order',$order);
         $pdf = PDF::loadView('backend.order.print');

  
        Mail::send('email.order-success', 
                    [
                    'email_template_content' => '<h3>Thanks For Order</h3>'
            ],function($message)use($data, $pdf) {
            $message->to($data["email"], $data["email"])
            ->subject($data["title"])
            ->attachData($pdf->output(), "invoice.pdf");
        });
        
        return true;
        
    }


    
}
