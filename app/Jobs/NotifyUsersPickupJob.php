<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use App\Models\UserDevice;
use App\Models\ClientPreference;
use App\Models\NotificationTemplate;

class NotifyUsersPickupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $database_name;
    protected $orders;
    protected $user_ids;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($database_name, $orders, $user_ids)
    {
        $this->database_name = $database_name;
        $this->orders = $orders;
        $this->user_ids = $user_ids;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client_preferences = ClientPreference::on($this->database_name)->first();
        $notification_content = NotificationTemplate::on($this->database_name)->where(['id' => 14])->first();

        foreach ($this->orders as $order) {
            $devices = UserDevice::on($this->database_name)->whereNotNull('device_token')->whereIn('user_id', $this->user_ids)->pluck('device_token')->toArray();

            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $body_content = str_ireplace("{order_id}", "#" . $order->order_number, $notification_content->content);
                if ($body_content) {
                    $devices = UserDevice::on($this->database_name)->whereNotNull('device_token')->where('user_id', $order->user_id ?? 0)->pluck('device_token')->toArray();
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            'sound' => "default",
                            "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action' => '',
                            "android_channel_id" => "default-channel-id"
                        ],
                        "data" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            'type' => "order_status_change"
                        ],
                        "priority" => "high"
                    ];
                    sendFcmCurlRequest($data);
                }
            }
        }
        
    }
}
