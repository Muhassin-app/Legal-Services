<?php

namespace App\Exports;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;

class CustomerExport implements FromCollection, WithHeadings, WithMapping
{
    protected $start_date;
    protected $end_date;

 function __construct(Request $request) {
    $this->start_date   = $request->start_date;
    $this->end_date     = $request->end_date;
 }

    public function collection(){

        if(!empty($this->start_date) && !empty($this->end_date)){
            $e_day      = date('Y-m-d', strtotime($this->end_date. ' + 1 day'));
            $start_date = Carbon::parse($this->start_date)->format('Y-m-d');
            $end_date   = Carbon::parse($e_day)->format('Y-m-d');
            $start_date = $start_date . ' 00:00:00';
            $end_date   = $end_date . ' 00:00:00';
            $query      = 'SELECT * FROM users WHERE EXISTS (SELECT 1 FROM orders WHERE orders.user_id = users.id AND orders.created_at >= ? AND orders.created_at <= ?)';
            $user_ids    = DB::select($query, [$start_date, $end_date]); 
            $user_ids    = array_column($user_ids, 'id');
            $users = User::with('orders')->withCount(['orders', 'currentlyWorkingOrders'])->whereNotIn('id',$user_ids)->where('is_superadmin', '!=', 1)->where('created_at', '<=', $end_date )
                ->orderBy('id', 'desc');

        }

        $current_user = Auth::user();
        $timezone = $current_user->timezone ? $current_user->timezone : 'Asia/Kolkata';
        $users = User::withCount(['orders', 'currentlyWorkingOrders'])->where('status', '!=', 3)->where('is_superadmin', '!=', 1)->orderBy('id', 'desc')->get();
        foreach ($users as  $user) {
            $user->image_url = $user->image['proxy_url'].'40/40'.$user->image['image_path'];
            $user->login_type = 'Email';
            $user->is_superadmin = $current_user->is_superadmin;
            $user->login_type_value = $user->email;
            if(!empty($user->facebook_auth_id)){
                $user->login_type = 'Facebook';
                $user->login_type_value = $user->facebook_auth_id;
            }elseif(!empty($user->twitter_auth_id)){
                $user->login_type = 'Twitter';
                $user->login_type_value = $user->twitter_auth_id;
            }elseif(!empty($user->google_auth_id)){
                $user->login_type = 'Google';
                $user->login_type_value = $user->google_auth_id;
            }elseif(!empty($user->apple_auth_id)){
                $user->login_type = 'Apple';
                $user->login_type_value = $user->apple_auth_id;
            }
        }
        return $users;
    }

    public function headings(): array{
        return [
            'Id',
            'Name',
            'Login Type',
            'Email/Auth-id',
            'Phone',
            'Wallet',
            'Orders Count',
            'Active Orders Count',
            'Status'
        ];
    }

    public function map($user_detail): array
    {   
        if($user_detail->dial_code){
            $dialcode =$user_detail->dial_code;
            $phone_number = '+ '. $user_detail->dial_code . $user_detail->phone_number;
            }else{
                $phone_number = $user_detail->phone_number;
            }

        
        return [
            $user_detail->id ?? "",
            $user_detail->name ?? "",
            $user_detail->login_type ?? "",
            $user_detail->login_type_value ?? "",
            $phone_number ?? "",
            $user_detail->balanceFloat ?? "",
            (!empty($user_detail->orders_count)) ? $user_detail->orders_count : "0",
            (!empty($user_detail->currently_working_orders_count)) ? $user_detail->currently_working_orders_count : "0",
            ($user_detail->status == 1)?"Active":"Not-active",
        ];
    }
}
