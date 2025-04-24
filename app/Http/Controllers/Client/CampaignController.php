<?php

namespace App\Http\Controllers\Client;

use Dotenv\Loader\Loader;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Campaign, CampaignRoster, Celebrity, Brand, Category, Country, User, UserVendor, Client, Timezone, UserDevice, Vendor, ClientPreference};
use Carbon\Carbon;
use Twilio\Rest\Client as TwilioClient;

class CampaignController extends BaseController
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $campaigns = Campaign::all();
        if ($campaigns) {
            if (count($campaigns) > 0) {
                $i = 0;
                foreach ($campaigns as $singlecampaign) {
                    $pendingnotification = CampaignRoster::where(['campaign_id' => $singlecampaign->id])->count();
                    $livecount = $singlecampaign->total_request_count - $pendingnotification;
                    $campaigns[$i]->livecount = $livecount ?? 0;
                    $i++;
                }
            }
        }
        return view('backend.campaign.index')->with(['campaigns' => $campaigns]);
    }

    // public function testnotification()
    // {
    //     $client_preferences = ClientPreference::first();
    //     $from = $client_preferences->fcm_server_key ?? "";
    //     $headers = [
    //         'Authorization: key=' . $from,
    //         'Content-Type: application/json',
    //     ];

    //     $intervalTime = Carbon::now();
    //     $notifications = CampaignRoster::where('notification_time', '<=', $intervalTime)->where('status',0)->with('campaign','user')->get();
    //             if($notifications)
    //             {
    //                 // //test sms
    //                 // $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
    //                 //     $to = '+919996687653';
    //                 //     $provider = $client_preferences->sms_provider;
    //                 //     $body = "Hi ".$client_preferences->sms_key;

    //                 //     $this->sendSms($provider, $client_preferences->sms_key, $client_preferences->sms_secret, $client_preferences->sms_from, $to, $body);


    //                 // //test email
    //                 // $sendto = "testu00091@gmail.com";
    //                 // $subject = "Test subject for email notification";
    //                 // $body = "test body message from notification";

    //                 // $email_data = [
    //                 //     'email' => $sendto,
    //                 //     'mail_from' => $client_preferences->mail_from,
    //                 //     'subject' => $subject,
    //                 //     'email_template_content' => $body,
    //                 //     'send_to_cc' => 0
    //                 // ];
    //                 // dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
    //                 // //$this->sendEmail($client_preferences,$sendto,$subject,$body);
    //                // CampaignRoster::where('id',6290)->delete();
    //                 foreach($notifications as $singlenotification)
    //                 {
    //                     //CampaignRoster::where('id',6290)->delete();
    //                     $type = $singlenotification->notofication_type;
    //                     //	type => 1 sms, 2 email, 3 push notification
    //                     switch ($type) {
    //                         case '1':
    //                             //send sms
    //                             try {
    //                                // $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
    //                                 if ($singlenotification->user->dial_code == "971") {
    //                                     $to = '+' . $singlenotification->user->dial_code . "0" . $singlenotification->user->phone_number;
    //                                 } else {
    //                                     $to = '+' . $singlenotification->user->dial_code . $singlenotification->user->phone_number;
    //                                 }
    //                                 $provider = $client_preferences->sms_provider;
    //                                 $body = "Hi " . $singlenotification->user->name . ", " . $singlenotification->campaign->sms_text;
    //                                 if (!empty($client_preferences->sms_provider)) {
    //                                     $send = $this->sendSms($provider, $client_preferences->sms_key, $client_preferences->sms_secret, $client_preferences->sms_from, $to, $body);
    //                                     if($send)
    //                                     {
    //                                         if($send==2)
    //                                         {
    //                                             //change status if failed
    //                                             CampaignRoster::where('id',$singlenotification->id)->update(array('status'=>2));
    //                                         }else{
    //                                             //remove notification if success
    //                                             CampaignRoster::where('id',$singlenotification->id)->delete();
    //                                         }
    //                                     }else{
    //                                         //change status if failed
    //                                         CampaignRoster::where('id',$singlenotification->id)->update(array('status'=>2));
    //                                     }
    //                                 }

    //                             } catch (\Exception $ex) {
    //                             }
    //                             break;
    //                         case '2':
    //                             //send email
    //                             try {
    //                                 if (!empty($client_preferences->mail_driver) && !empty($client_preferences->mail_host) && !empty($client_preferences->mail_port) && !empty($client_preferences->mail_password) && !empty($client_preferences->mail_encryption)) {
    //                                     $useremail = $singlenotification->user->email;
    //                                     $email_subject = $singlenotification->campaign->email_subject;
    //                                     $email_body = $singlenotification->campaign->email_body;

    //                                     $email_data = [
    //                                         'email' => $useremail,
    //                                         'mail_from' => $client_preferences->mail_from,
    //                                         'subject' => $email_subject,
    //                                         'email_template_content' => $email_body,
    //                                         'send_to_cc' => 0
    //                                     ];
    //                                     $sendemail = dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
    //                                     if($sendemail)
    //                                     {
    //                                         //remove notification if success
    //                                         CampaignRoster::where('id',$singlenotification->id)->delete();
    //                                     }else{
    //                                        //change status if failed
    //                                         CampaignRoster::where('id',$singlenotification->id)->update(array('status'=>2));
    //                                     }
    //                                     //$this->sendEmail($client_preferences,$useremail,$email_subject,$email_body);
    //                                 }
    //                             } catch (\Exception $ex) {
    //                             }
    //                             break;
    //                         case '3':
    //                             //send push
    //                             //$redirect_URL = "https://" . $client->sub_domain . env('SUBMAINDOMAIN') . "/viewcart";
    //                             $redirect_URL = $singlenotification->campaign->push_url_option_value;
    //                             $data = [
    //                                 "registration_ids" => [$singlenotification->device_token],
    //                                 "notification" => [
    //                                     'title' => $singlenotification->campaign->push_title,
    //                                     'body'  => $singlenotification->campaign->push_message_body,
    //                                     'sound' => "default",
    //                                     "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
    //                                     'click_action' => $redirect_URL,
    //                                     "android_channel_id" => "default-channel-id"
    //                                 ],
    //                                 "data" => [
    //                                     'title' => $singlenotification->campaign->push_title,
    //                                     'body'  => $singlenotification->campaign->push_message_body,
    //                                     'type' => "reminder_notification"
    //                                 ],
    //                                 "priority" => "high"
    //                             ];
    //                             $dataString = $data;
    //                             $ch = curl_init();
    //                             curl_setopt($ch, CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send');
    //                             curl_setopt($ch, CURLOPT_POST, true);
    //                             curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    //                             curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //                             curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    //                             curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dataString));
    //                             $result = curl_exec($ch);
    //                             //// Log::info($result);
    //                             curl_close($ch);
    //                             $result;
    //                             if($result)
    //                             {
    //                                 //remove notification if success
    //                                 CampaignRoster::where('id',$singlenotification->id)->delete();
    //                             }else{
    //                                 //change status if failed
    //                                 CampaignRoster::where('id',$singlenotification->id)->update(array('status'=>2));
    //                             }
    //                         break;
    //                     }
    //                 }
    //             }
    // }

    // protected function sendSms($provider, $sms_key, $sms_secret, $sms_from, $to, $body){
    //     try{
    //         $client_preference =  getClientPreferenceDetail();
    //         if($client_preference->sms_provider == 1)
    //         {
    //             $client = new TwilioClient($sms_key, $sms_secret);
    //             $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
    //         }elseif($client_preference->sms_provider == 2) //for mtalkz gateway
    //         {
    //             $crendentials = json_decode($client_preference->sms_credentials);
    //             $send = $this->mTalkz_sms($to,$body,$crendentials);
    //         }elseif($client_preference->sms_provider == 3) //for mazinhost gateway
    //         {
    //             $crendentials = json_decode($client_preference->sms_credentials);
    //             $send = $this->mazinhost_sms($to,$body,$crendentials);
    //         }elseif($client_preference->sms_provider == 4) //for unifonic gateway
    //         {
    //             $crendentials = json_decode($client_preference->sms_credentials);
    //             $send = $this->unifonic($to,$body,$crendentials);
    //         }else{
    //             $client = new TwilioClient($sms_key, $sms_secret);
    //             $client->messages->create($to, ['from' => $sms_from, 'body' => $body]);
    //         }
    //     }
    //     catch(\Exception $e){
    //         return '2';
    //     }
    //     return '1';
    // }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //dd($request->all());

        // $timezonedetail = Client::with('getTimezone')->first('timezone');
        // $tz = new Timezone();
        // $usertimezone = $tz->timezone_name($timezonedetail->timezone);
        // echo $notification_time = Carbon::parse($request->schedule_datetime . $usertimezone ?? 'UTC')->tz('UTC');
        //  return $newnotification_time = $notification_time->addMinute($request->request_time_gap);

        $rules = array(
            // 'slug' => 'required|string|max:30|unique:celebrities',
            'title' => 'required|string|max:190',
            'schedule_datetime' => 'required'
        );
        /* upload logo file */
        if ($request->hasFile('push_image')) {
            $rules['push_image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $campaign = new Campaign();
        $campaign->title = $request->title;
        $campaign->type = $request->type;
        if ($request->type == 1) {
            $campaign->sms_text = $request->sms_text;
        } elseif ($request->type == 2) {
            // $campaign->email_title = $request->email_title;
            $campaign->email_subject = $request->email_subject;
            $campaign->email_body = $request->email_body;
        } else {
            $campaign->push_title = $request->push_title ?? $request->title;
            $campaign->push_message_body = $request->push_message_body;
            $campaign->push_url_option = $request->push_url_option;

            $option = '';
            if ($request->push_url_option == '2') {
                $categorySlug = Category::where('id', $request->push_url_option_value)->first();
                $option = $categorySlug->type->title . '/' . $categorySlug->translation_one->name . '/' . $request->push_url_option_value;
            } else if ($request->push_url_option == '3') {
                $vendorSlug = Vendor::where('id', $request->push_url_option_value)->select('name')->first();
                $option = 'Vendor/' . $vendorSlug->name . '/' . $request->push_url_option_value;
            }
            // $client = Client::select('sub_domain','custom_domain')->where('id', '>', 0)->first();
            // if(isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain)
            // $redirect_url_link = "https://" . $client->custom_domain.$option;
            // else
            // $redirect_url_link = "https://" . $client->sub_domain . env('SUBMAINDOMAIN').$option;

            $campaign->push_url_option_value = (($request->push_url_option == '1') ? $request->push_url_option_value : $option);
        }
        $campaign->send_to = $request->send_to;
        $campaign->schedule_datetime = $request->schedule_datetime;
        $campaign->request_user_count = $request->request_user_count;
        $campaign->request_time_difference = $request->request_time_gap;
        $campaign->status = 1;
        if ($request->type == 3) {
            if ($request->hasFile('push_image')) {
                $file = $request->file('push_image');
                $images = Storage::disk('s3')->put('/notification', $file, 'public');
                $campaign->push_image = $images;
            }
        }
        $campaign->save();
        if ($campaign->id > 0) {
            $usertype = $request->send_to;
            $timezonedetail = Client::with('getTimezone')->first('timezone');
            $tz = new Timezone();
            $usertimezone = $tz->timezone_name($timezonedetail->timezone);
            $notification_time = Carbon::parse($request->schedule_datetime . $usertimezone ?? 'UTC')->tz('UTC');
            $notification_type = $request->type;
            if ($usertype == 1)    // for all users
            {
                $users = User::where(['status' => 1]);
                  $rosterData = $this->getRosterData($request, $users, $campaign->id, $notification_time, $notification_type);
            } else {  //for vendors only
                $vendors = User::whereHas('userVendor')->where(['status' => 1]);
                $rosterData = $this->getRosterData($request, $vendors, $campaign->id, $notification_time, $notification_type);
                }
                if($rosterData){
                    $total_requests = CampaignRoster::where('campaign_id', $campaign->id)->count();
                    Campaign::where('id', $campaign->id)->update(['total_request_count' => $total_requests]);

                    return response()->json([
                        'status' => 'success',
                        'message' => 'Campaign created Successfully!',
                        'data' => $campaign
                    ]);
                }
                return response()->json([
                    'status' => 'error',
                    'message' => 'Something went wrong, Please try again!',
                    'data' => []
                ]);

        }
    }
    public function getRosterData($request, $users, $campaign_id, $notification_time, $notification_type)
    {
        if ($request->type == 1) {
            $users->whereNotNull('phone_number')->groupBy('phone_number');
        }
        if ($request->type == 2) {
            $users->whereNotNull('email')->groupBy('email');
        }
        if ($request->type == 3) {
            $users->whereHas('device');
        }
        $users = $users->pluck('id');
        $getusercount = count($users);
        $totalbatches = ceil($getusercount / $request->request_user_count);
        for ($i = 1; $i <= $totalbatches; $i++) {
            $usercount = $request->request_user_count;
            if ($i == 1) {
                $notification_time = $notification_time;
            } else {
                $notification_time = $notification_time->addMinute($request->request_time_gap);
            }
            $roasterdata = [];
              $conditionalvalue = (($i * $usercount) <= $getusercount) ? $i * $usercount : $getusercount;
            for ($j = (($i - 1) * $usercount); $j < $conditionalvalue; $j++) {
                if ($request->type == 3) {
                    $getdevicedetails = UserDevice::where('user_id', $users[$j])->pluck('device_token');
                    if (count($getdevicedetails) > 0) {
                        foreach ($getdevicedetails as  $getdevicedetail) {
                            $roasterdata[] = array(
                                'campaign_id'   =>  $campaign_id,
                                'user_id'   =>  $users[$j],
                                'notification_time'   =>  $notification_time,
                                'notofication_type'   =>  $notification_type,
                                'device_type'   =>  '',
                                'device_token'   =>  $getdevicedetail,
                                'status'    =>  0
                            );
                        }
                    }
                } else {
                    $roasterdata[] = array(
                        'campaign_id'   =>  $campaign_id,
                        'user_id'   =>  $users[$j],
                        'notification_time'   =>  $notification_time,
                        'notofication_type'   =>  $notification_type,
                        'device_type'   =>  "",
                        'device_token'   =>  "",
                        'status'    =>  0
                    );
                }
            }

            $insertroaster = CampaignRoster::insert($roasterdata);
        }
        return $insertroaster;
    }
    public function GetPushOptions(Request $request)
    {
        $pushoption =  $request->pushvalue;
        $html = '<select class="form-control" name="push_url_option_value" id="push_url_option_value"> ';
        if ($pushoption == 2)  //categories
        {
            $getcategories = Category::where('status', 1)->with('translation_one')->get(['id', 'slug']);
            foreach ($getcategories as $singlecategory) {
                $html .= '<option value="' . $singlecategory->id . '">' . $singlecategory->translation_one->name . '</option>';
            }
        } elseif ($pushoption == 3) //vendors
        {
            $getvendors = Vendor::where('status', 1)->get(['id', 'name', 'slug']);
            foreach ($getvendors as $singlevendor) {
                $html .= '<option value="' . $singlevendor->id . '">' . $singlevendor->name . '</option>';
            }
        } else {
        }
        $html .= '</select>';
        $result = array('html' => $html);
        echo json_encode($result);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($domain = '', $id)
    {
        $campaign = Campaign::where('id', $id)->first();

        $returnHTML = view('backend.campaign.form')->with(['campaign' => $campaign])->render();
        return response()->json(array('success' => true, 'html' => $returnHTML));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($domain = '', Request $request, $id)
    {
        $rules = array(
            'slug' => 'required|string|max:30|unique:categories,slug,' . $id,
            'name' => 'required|string|max:150',
        );
        if ($request->hasFile('image')) {
            $rules['image'] =  'image|mimes:jpeg,png,jpg,gif';
        }
        $validation  = Validator::make($request->all(), $rules)->validate();
        $celebrity = Celebrity::where('id', $id)->firstOrFail();
        $celebrity->name = $request->input('name');
        $celebrity->slug = $request->input('slug');
        $celebrity->country_id = $request->input('countries');
        $celebrity->description = $request->description;
        $celebrity->status = '1';
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $images = Storage::disk('s3')->put('/celebrity', $file, 'public');
            $celebrity->avatar = $images;
        }

        $celebrity->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Celebrity created Successfully!',
            'data' => $celebrity
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($domain = '', $id)
    {
        Campaign::where('id', $id)->delete();
        CampaignRoster::where('campaign_id', $id)->delete();
        return redirect()->back()->with('success', 'Campaign deleted successfully!');
    }

    /**
     * Change the status of Loyalty card.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus(Request $request, $domain = '')
    {
        $loyaltyCard = Celebrity::find($request->id);
        $loyaltyCard->status = $request->status;
        $loyaltyCard->save();
        return response()->json(array('success' => true, 'data' => $loyaltyCard));
    }

    /**
     * Get the default value of Redeem Point
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getBrandList($domain = '')
    {
        $brands = Brand::all();
        return response()->json(['brands' => $brands]);
    }
}
