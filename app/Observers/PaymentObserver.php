<?php

namespace App\Observers;

use App\Models\Payment;
use App\Models\TrackEvent;
use App\Models\User;

class PaymentObserver
{
    /**
     * Handle the Payment "created" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function created(Payment $payment)
    {
        $payment_info = json_decode(json_encode($payment->toArray()), true);

            $uid = $payment_info['user_id']??null;
            $uname = "";

        if(@$uid){
            $userInfo = User::where('id', $uid)->first();
            if($userInfo) {
               $uname = $userInfo->name ?? 'N/A';
            }
        }

        $data = array(
            'location' => 'payment-call',
            'details' => 'User-Id : '.$uid.', Name : '.$uname??'N/A'.', Date : '.date('d-m-Y H:i:a').', '.json_encode($payment->toArray()),
        );
       TrackEvent::create($data);

    
    }


    /**
     * Handle the Payment "updated" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function updated(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "deleted" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function deleted(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "restored" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function restored(Payment $payment)
    {
        //
    }

    /**
     * Handle the Payment "force deleted" event.
     *
     * @param  \App\Models\Payment  $payment
     * @return void
     */
    public function forceDeleted(Payment $payment)
    {
        //
    }
}
