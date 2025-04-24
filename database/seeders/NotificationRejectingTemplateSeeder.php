<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use Illuminate\Support\Str;

class NotificationRejectingTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if(!NotificationTemplate::find(11)){
            NotificationTemplate::updateOrCreate(['id' => 11],['label' => "Order Reject (Vendor)", 'slug' => 'order-rejected-vendor', 'subject' => "Order Rejecting", 'content' => "Your order ({order_id}) has been rejecting after 5 min", 'tags' => "{order_id}"]);
        }

        if(!NotificationTemplate::find(12)){
            NotificationTemplate::updateOrCreate(['id' => 12],['label' => "Order Modified (Customer)", 'slug' => 'order-modified-customer', 'subject' => "Order Modified", 'content' => "Your order ({order_id}) has been modified", 'tags' => "{order_id}"]);
        }

        if(!NotificationTemplate::find(13)){
            NotificationTemplate::updateOrCreate(['id' => 13],['label' => "Order Cancellation Request (Owner)", 'slug' => 'order-cancellation-request', 'subject' => "Order Cancellation Request", 'content' => "Order ({order_id}) cancellation request has been received", 'tags' => "{order_id}"]);
        }

    }
}
