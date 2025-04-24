<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OrderLoyaltyExport implements FromCollection,WithHeadings,WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection(){
        $user = Auth::user();
        $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $orders = Order::with('user','paymentOption','loyaltyCard')->orderBy('id', 'desc');
        if (Auth::user()->is_superadmin == 0) {
            $orders = $orders->whereHas('vendors.vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        $orders = $orders->get();

        foreach ($orders as $order) {
            $order->loyalty_membership = $order->loyaltyCard ? $order->loyaltyCard->name : '';
            $order->loyalty_points_used = $order->loyalty_points_used ? $order->loyalty_points_used : '0.00';
            $order->created_date = dateTimeInUserTimeZone($order->created_at, $timezone);
            $order->loyalty_points_earned = $order->loyalty_points_earned ? $order->loyalty_points_earned : '0.00';
        }
        return $orders;
    }
    public function headings(): array{
        return [
            'Order Id',
            'Date & Time',
            'Customer Name',
            'Final Amount',
            'Loyalty Used',
            'Loyality Membership',
            'Loyality Earned',
            'Payment Method',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_number,
            $order->created_date,
            $order->user ? $order->user->name : '',
            $order->payable_amount,
            $order->loyalty_points_used,
            $order->loyalty_membership,
            $order->loyalty_points_earned,
            $order->paymentOption ? $order->paymentOption->title: '',
        ];
    }
}
