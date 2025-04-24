<?php

namespace App\Exports;
use App\Models\OrderVendor;
use App\Models\OrderStatusOption;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\ClientPreference;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Session;

class OrderVendorListTaxExport implements FromCollection,WithHeadings,WithMapping{
    /**
    * @return \Illuminate\Support\Collection
    */

    public $data;

    public function __construct($request)
    {
        $this->data = (object)$request->input();
    }

    public function collection(){
        $user = Auth::user();
        if (Session::has('preferences') && !empty(Session::get('preferences'))) {
            $client_preference_detail = (object)Session::get('preferences');
            if(!isset($client_preference_detail->is_tax_price_inclusive)){
                $client_preference_detail =  (object)getAdditionalPreference(['is_tax_price_inclusive']);
            }
        }else{
            $client_preference_detail = (object)getAdditionalPreference(['is_tax_price_inclusive']);
        }
        $timezone = $user->timezone ? $user->timezone : 'Asia/Kolkata';
        $vendor_orders =  OrderVendor::with(['orderDetail.paymentOption', 'user','vendor','payment'])->orderBy('id', 'DESC');
        if (Auth::user()->is_superadmin == 0){
            $vendor_orders = $vendor_orders->whereHas('vendor.permissionToUser', function ($query) {
                $query->where('user_id', Auth::user()->id);
            });
        }
        if(isset($this->data->date_range)){
            $date = explode(' to ',$this->data->date_range);
            $dateF = $date[0];
            $dateT = !empty($date[1]) ?$date[1]: $date[0];
            $dateF = Carbon::parse($dateF, $timezone)->setTimezone('UTC');
            $dateT = Carbon::parse($dateT, $timezone)->setTimezone('UTC')->addDays(1);
            $vendor_orders = $vendor_orders->whereBetween('created_at',[$dateF, $dateT]);
        }
        if(isset($this->data->vendor)){
            $vendor = $this->data->vendor;
            $vendor_orders = $vendor_orders->where('vendor_id',$vendor);
        }
        if(isset($this->data->order_status)){
            $status = $this->data->order_status;
            if($this->data->order_status =='Placed'){
                $status = '1';
            }elseif($this->data->order_status =='Accepted'){
                $status = '2';
            }elseif($this->data->order_status =='Out For Delivery'){
                $status = '5';
            }elseif($this->data->order_status =='Rejected'){
                $status = '3';
            }elseif($this->data->order_status =='Processing'){
                $status = '4';
            }elseif($this->data->order_status =='Delivered'){
                $status = '6';
            }
            $vendor_orders = $vendor_orders->where('order_status_option_id',$status);
        }
        $vendor_orders = $vendor_orders->get();
        foreach ($vendor_orders as $vendor_order) {


            $adminDiscount = 0.00;
            $vendor_order->created_date = dateTimeInUserTimeZone($vendor_order->created_at, $timezone);
            $vendor_order->user_name = $vendor_order->user ? $vendor_order->user->name : '';
            $order_status = '';
            if($vendor_order->orderstatus){
                $order_status_detail = $vendor_order->orderstatus->where('order_id', $vendor_order->order_id)->orderBy('id', 'DESC')->first();
                if($order_status_detail){
                    $order_status_option = OrderStatusOption::where('id', $order_status_detail->order_status_option_id)->first();
                    if($order_status_option){
                        $order_status = $order_status_option->title;
                    }
                }
            }
            $tip = !empty($vendor_order->orderDetail)?number_format($vendor_order->orderDetail->tip_amount, 2):0.00;
            if ($vendor_order->coupon_paid_by == 1) {
                $adminDiscount = $vendor_order->discount_amount;
            }
            $vendor_order->total_amount = (double)$tip + (double)$vendor_order->payable_amount;
            $vendor_order->cash_payment = 0;
            if ($vendor_order->orderDetail->payment_option_id == 1) {
                $vendor_order->cash_payment = $vendor_order->payable_amount;
            }
            if(!empty($vendor_order->orderDetail)){
                $vendor_order->taxable_amount  =   (float) array_sum(explode(":", $vendor_order->orderDetail->total_other_taxes));
            }else{
                $vendor_order->taxable_amount = $vendor_order->orderDetail->total_other_taxes_amount;
            }

            $vendor_order->taxable_amount  = round($vendor_order->taxable_amount,2);
            $vendor_order->cash_payment  = $vendor_order->cash_payment;

            $vendor_order->order_status = $order_status;
            $revenue = $vendor_order->admin_commission_percentage_amount + $vendor_order->admin_commission_fixed_amount + $vendor_order->total_markup_price;
            $vendor_order->online_payment = isset($vendor_order->payment) ? $vendor_order->payment->balance_transaction :'';
            if(@$client_preference_detail->is_tax_price_inclusive){
                $vendor_order->admin_revenue = ($revenue + $vendor_order->total_container_charges + $vendor_order->service_fee_percentage_amount + $vendor_order->delivery_fee) - $adminDiscount - number_format($vendor_order->orderDetail->loyalty_amount_saved??0.00);
            }else{
                $vendor_order->admin_revenue = ($revenue + $vendor_order->taxable_amount +$vendor_order->total_container_charges+  $vendor_order->service_fee_percentage_amount  + $vendor_order->delivery_fee) - $adminDiscount - decimal_format($vendor_order->orderDetail->loyalty_amount_saved??0.00);
            }
        }
        return $vendor_orders;
    }

    public function headings(): array{
        if(Auth::user()->is_superadmin)
        {
            return [
                'Customer ID',
                'Order ID',
                'Transaction ID',
                'Date & Time',
                'Customer Name',
                'Vendor Name',
                'Subtotal Amount',
                'Wallet',
                'Cash',
                'Online',
                'Total Discount',
                'Promo Code Used',
                'Promo Code Discount',
                'Service Fee',
                'Delivery Fee',
                'Fixed Fee',
                'Tip Amount',
                'Sales Tax',
                'Vendor Earning',
                'Admin Commission [Fixed]',
                'Admin Commission [%Age]',
                'Final Amount',
                'Redeemed Loyality Points',
                'Rewarded Loyality Points',
                'Platform Revenue',
                'Payment Method',
                'Order Status',
                'Delivery Mode',
                'Pickup Address',
                'Delivery Address'
            ];
        }else{
            return [
                'Customer ID',
                'Order ID',
                'Transaction ID',
                'Date & Time',
                'Customer Name',
                'Vendor Name',
                'Subtotal Amount',
                'Wallet',
                'Cash',
                'Online',
                'Total Discount',
                'Promo Code Used',
                'Promo Code Discount',
                'Service Fee',
                'Delivery Fee',
                'Fixed Fee',
                'Tip Amount',
                'Sales Tax',
                'Vendor Earning',
                'Admin Commission [Fixed]',
                'Admin Commission [%Age]',
                'Final Amount',
                'Payment Method',
                'Order Status',
                'Delivery Mode',
                'Delivery Address'
            ];

        }
    }

    public function map($order_vendors): array
    {
        if(Auth::user()->is_superadmin)
        {
            return [
                $order_vendors->user_id,
                $order_vendors->orderDetail ? $order_vendors->orderDetail->order_number : '',
                $order_vendors->payment ? $order_vendors->payment->transaction_id : '',
                $order_vendors->created_date,
                $order_vendors->user_name,
                $order_vendors->vendor ? $order_vendors->vendor->name : '',
                decimal_format($order_vendors->subtotal_amount),
                decimal_format($order_vendors->orderDetail ? $order_vendors->orderDetail->wallet_amount_used : 0),
                decimal_format($order_vendors->cash_payment),
                decimal_format(floatval($order_vendors->online_payment)),
                decimal_format(floatval($order_vendors->orderDetail->total_discount ?? 0)),
                $order_vendors->coupon_code,
                decimal_format($order_vendors->discount_amount),
                decimal_format($order_vendors->service_fee_percentage_amount),
                decimal_format($order_vendors->delivery_fee),
                decimal_format($order_vendors->fixed_fee),
                $order_vendors->orderDetail ? $order_vendors->orderDetail->tip_amount : '',
                decimal_format($order_vendors->taxable_amount),
                $order_vendors->vendor_amount,
                decimal_format($order_vendors->admin_commission_fixed_amount),
                decimal_format($order_vendors->admin_commission_percentage_amount),
                decimal_format($order_vendors->total_amount),
                $order_vendors->orderDetail ? $order_vendors->orderDetail->loyalty_points_used : '',
                $order_vendors->orderDetail ? $order_vendors->orderDetail->loyalty_points_earned : '',
                decimal_format($order_vendors->admin_revenue),
                ($order_vendors->orderDetail && $order_vendors->orderDetail->paymentOption) ? $order_vendors->orderDetail->paymentOption->title : '',
                $order_vendors->order_status,
                $order_vendors->orderDetail->shipping_delivery_type == 'L' ?'Lalamove' :'Dispatcher',
                $order_vendors->vendor ? $order_vendors->vendor->address ?? '' : '',
                $order_vendors->orderDetail ? (($order_vendors->orderDetail->address)?$order_vendors->orderDetail->address->fullAddress : '') : '',
            ];
        }else{
            return [
                $order_vendors->user_id,
                $order_vendors->orderDetail ? $order_vendors->orderDetail->order_number : '',
                $order_vendors->payment ? $order_vendors->payment->transaction_id : '',
                $order_vendors->created_date,
                $order_vendors->user_name,
                $order_vendors->vendor ? $order_vendors->vendor->name : '',
                decimal_format($order_vendors->subtotal_amount),
                decimal_format($order_vendors->orderDetail ? $order_vendors->orderDetail->wallet_amount_used : 0),
                decimal_format($order_vendors->cash_payment),
                decimal_format(floatval($order_vendors->online_payment)),
                decimal_format(floatval($order_vendors->orderDetail->total_discount ?? 0)),
                $order_vendors->coupon_code,
                decimal_format($order_vendors->discount_amount),
                decimal_format($order_vendors->service_fee_percentage_amount),
                decimal_format($order_vendors->delivery_fee),
                decimal_format($order_vendors->fixed_fee),
                $order_vendors->orderDetail ? $order_vendors->orderDetail->tip_amount : '',
                decimal_format($order_vendors->taxable_amount),
                $order_vendors->vendor_amount = 20.12,
                decimal_format($order_vendors->admin_commission_fixed_amount),
                decimal_format($order_vendors->admin_commission_percentage_amount),
                decimal_format($order_vendors->total_amount),
               ($order_vendors->orderDetail && $order_vendors->orderDetail->paymentOption)? $order_vendors->orderDetail->paymentOption->title : '',
                $order_vendors->order_status,
                $order_vendors->orderDetail->shipping_delivery_type == 'L' ?'Lalamove' :'Dispatcher',
                $order_vendors->vendor ? $order_vendors->vendor->address ?? '' : '',
                $order_vendors->orderDetail ? (($order_vendors->orderDetail->address)?$order_vendors->orderDetail->address->fullAddress : '') : '',
            ];

        }
    }
}
