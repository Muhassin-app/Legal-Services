<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\NotificationTemplate;
use Illuminate\Support\Str;

class NotificationTemplateSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $create_array = [
            [
                'label' =>'New Order',
                'subject' =>'New Vendor Signup',
                'tags' => '',
                'content' => 'Thanks for your Order',
                'slug' => 'new-order'
            ],
            [
                'label' => 'Order Status Update',
                'subject' => 'Verify Mail',
                'tags' => '',
                'content' => 'Your Order status has been updated',
                'slug' => 'order-status-update'
            ],
            [
                'label' =>'Refund Status Update',
                'subject' => 'Reset Password Notification',
                'tags' => '',
                'content' => 'Your Order status has been updated',
                'slug' => 'refund-status-update'
            ],
            [
                'label' =>'New Order Received (Owner)',
                'subject' => 'New Order Received',
                'tags' => '',
                'content' => 'You have received a new order',
                'slug' => 'new-order-received'
            ],
            [
                'label' =>'Order Accepted (Customer)',
                'subject' => 'Order Accepted',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) has been accepted',
                'slug' => 'order-accepted'
            ],
            [
                'label' =>'Order Rejected (Customer)',
                'subject' => 'Order Rejected',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) has been rejected',
                'slug' => 'order-rejected'
            ],
            [
                'label' =>'Meeting Scheduled(Customer)',
                'subject' => 'Meeting Scheduled',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) has been scheduled for a meeting.',
                'slug' => 'meeting-scheduled'
            ],
            [
                'label' =>'Out of delivery (Customer)',
                'subject' => 'Out of delivery',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) has been reached to you soon',
                'slug' => 'order-out-of-delivery'
            ],
            [
                'label' =>'Order Delivered (Customer)',
                'subject' => 'Order Delivered',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) has delivered',
                'slug' => 'order-delivered'
            ],
            [
                "label" => "Place Order Reminder (Customer)",
                "subject" => "Don't wait too much",
                "tags" => "",
                "content" => "Place your order before it's too late",
                "slug" => "place-order-reminder"
            ],
            [
                "label" => "Place Bid Request (Customer)",
                "subject" => "You have new bid request",
                "tags" => "{prescription}",
                "content" => "Place your bid before it's too late {prescription}",
                "slug" => "place-bid-request"
            ],
            [
                "label" => "Order Modified (Customer)",
                "subject" => "Order Modified",
                "tags" => "{order_id}",
                "content" => "Your order ({order_id}) has been modified",
                "slug" => "order-modified-customer"
            ],
            [
                "label" => "Order Delayed (Customer)",
                "subject" => "Order Delayed",
                "tags" => "{order_id}",
                "content" => "Your order ({order_id}) has been Delayed",
                "slug" => "order-delayed-customer"
            ],
            [
                "label" => "Pickup Delivery Reminder",
                "subject" => "Pickup Delivery",
                "tags" => "{order_id}",
                "content" => "Your order ({order_id}) has been reached to you soon",
                "slug" => "pickup-delivery-customer"
            ],
            [
                "label" => "Reached Vendor Location",
                "subject" => "Reached Vendor Location",
                "tags" => "{order_id}",
                "content" => "Location reached",
                "slug" => "reached-vendor-location"
            ],
            [
                'label' =>'Out of takeaway-delivery (Customer)',
                'subject' => 'Out of takeaway-delivery',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) is ready for pickup',
                'slug' => 'order-out-for-takeaway-delivery'
            ],
            [
                'label' =>'Order Cancelled',
                'subject' => 'Order Cancelled',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) is canecelled by driver',
                'slug' => 'order-cancelled'
            ],
            [
                'label' =>'Product Out Of Stock (Vendor)',
                'subject' => 'Product Out Of Stock',
                'tags' => '',
                'content' => 'Products are finishing! || You are running out of products!',
                'slug' => 'product-stock-vendor'
            ],
            [
                'label' =>'Order Cancelled (Vendor)',
                'subject' => 'Order Cancelled',
                'tags' => '{order_id}',
                'content' => 'Your order ({order_id}) is canecelled by Admin',
                'slug' => 'order-cancelled-vendor'
            ],
            [
                'label' =>'Document Reviewed(Customer)',
                'subject' => 'Document Reviewed',
                'tags' => '{order_id}',
                'content' => 'Your document has been Reviewed by the lawyer',
                'slug' => 'document-reviewed'
            ],
            [
                'label' =>'Lawyer Submitted Document',
                'subject' => 'Document Submitted',
                'tags' => '{order_id}',
               'content' => 'document has been submitted by the lawyer.',
                'slug' => 'document-submitted'
            ],
            [
                'label' =>'Documents Accepted By Client',
                'subject' => 'Document Accepted',
                'tags' => '{order_id}',
               'content' => 'document has been accepted by the client.',
                'slug' => 'document-accepted'
            ],
            [
                'label' =>'Documents Rejected By Client',
                'subject' => 'Documents rejected by Client',
                'tags' => '{order_id}',
               'content' => 'document has been rejected by the client.',
                'slug' => 'document-rejected'
            ],
            [
                'label' =>'Service Completed',
                'subject' => 'Service Completed Successfully',
                'tags' => '{order_id}',
               'content' => 'your service has successfully completed',
                'slug' => 'service-completed'
            ],
            [
                'label' =>'Payment Received',
                'subject' => 'Payment Received Successfully',
                'tags' => '{order_id}',
               'content' => 'you have received the payment',
                'slug' => 'service-completed'
            ],
            [
                'label' =>'Lawyer Assigned',
                'subject' => 'Lawyer Assigned',
                'tags' => '{order_id}',
               'content' => 'Lawyer has been assigned to your order',
                'slug' => 'lawyer-assigned'
            ]
        ];
        NotificationTemplate::truncate();
        foreach ($create_array as $key => $array) {
            NotificationTemplate::create(['label' => $array['label'], 'slug' => $array['slug'], 'content' => $array['content'], 'subject' => $array['subject'], 'tags' => $array['tags']]);
        }
    }
}
