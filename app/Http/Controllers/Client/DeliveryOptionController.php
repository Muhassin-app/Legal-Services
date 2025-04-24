<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Client\BaseController;
use App\Http\Traits\ToasterResponser;
use App\Models\{Client, ClientPreference, ShippingOption};
use Illuminate\Http\Request;
use GuzzleHttp\Client as GCLIENT;
use Auth;

class DeliveryOptionController extends Controller
{
    use \App\Http\Traits\ClientPreferenceManager;
    use ToasterResponser;

    public function index()
    {
        $shippingOptionCodes = ['lalamove', 'shiprocket', 'dunzo', 'ahoy', 'shippo', 'kwikapi', 'roadie','shipengine','borzo'];
        $shippingOptions = ShippingOption::whereIn('code', $shippingOptionCodes)->get()->keyBy('code');
        $preference = ClientPreference::select('id', 'need_delivery_service', 'delivery_service_key_url', 'delivery_service_key_code', 'delivery_service_key', 'last_mile_team')->first();
        # if last mile on
        $last_mile_teams = [];
        if ($preference && $preference->need_delivery_service == '1') {
            $last_mile_teams = $this->getLastMileTeams();
        }
        $d4b_dunzo = ShippingOption::where('code', 'd4b_dunzo')->first();

        return view('backend/deliveryoption/index')->with([
            'delOption' => $shippingOptions->get('lalamove'),
            'opt' => $shippingOptions->get('shiprocket'),
            'optDunzo' => $shippingOptions->get('dunzo'),
            'optAhoy' => $shippingOptions->get('ahoy'),
            'shippoOption' => $shippingOptions->get('shippo'),
            'last_mile_teams' => $last_mile_teams,
            'preference' => $preference,
            'kwikOption' => $shippingOptions->get('kwikapi'),
            'roadieOption' => $shippingOptions->get('roadie'),
            'shipEngineOption' => $shippingOptions->get('shipengine'),
            'd4b_dunzo'=>$d4b_dunzo,
            'borzoOption' => $shippingOptions->get('borzo'),
        ]);
    }

    //Set new dunzo configuration details function
    public function dunzo(Request $request)
    {

        try {
            //dd($request->input());
            $msg = 'Dunzo delivery details have been saved successfully!';
            $id = $request->method_id;
            $method_name_arr = $request->method_name;
            $active_arr = $request->active;
            $base_active = $request->base_active;
            $test_mode_arr = $request->sandbox;

            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'dunzo')) {
                    $validatedData = $request->validate([
                        'api_key' => 'required',
                        'app_url' => 'required',
                    ]);
                    $json_creds = array(
                        'api_key' => $request->api_key,
                        'app_url' => (($test_mode == '1') ? 'https://dev.adloggs.com/aa' : 'https://app.adloggs.com/aa'),
                    );
                    //dd($json_creds);

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price;
                        $json_creds['distance'] = $request->distance;
                        $json_creds['amount_per_km'] = $request->amount_per_km;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }
                    $json_creds = json_encode($json_creds);
                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);

    }

    //Set new dunzo configuration details function
    public function d4b_dunzo(Request $request)
    {
     try{
         $msg = 'Dunzo delivery details have been saved successfully!';
         $id = $request->method_id;
         $method_name_arr = $request->method_name;
         $active_arr = $request->active;
         $base_active = $request->base_active;
         $test_mode_arr = $request->sandbox;

         $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();

         if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {

                 $json_creds = $saved_creds->credentials;
             } else {
                 $json_creds = NULL;
         }

             $status = 0;
             $test_mode = 0;
             if ((isset($active_arr)) && ($active_arr == 'on')) {

                 $status = 1;

                 if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                     $test_mode = 1;
                 }

                 if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'd4b_dunzo')) {
                     $validatedData = $request->validate([
                         'client_id'               => 'required',
                         'client_secret'               => 'required',
                         'app_url'               => 'required',
                     ]);
                     $json_creds = array(
                         'client_id'               => $request->client_id,
                         'client_secret'               => $request->client_secret,
                         'app_url'               => (($test_mode=='1')?'https://apis-staging.dunzo.in/api/v1/token':'https://api.dunzo.in/api/v1/token'),
                     );
                     //dd($json_creds);

                     if ((isset($base_active)) && ($base_active == 'on')) {
                         $json_creds['base_price'] = $request->base_price;
                         $json_creds['distance'] = $request->distance;
                         $json_creds['amount_per_km'] = $request->amount_per_km;
                     }else{
                         $json_creds['base_price'] = '0';
                         $json_creds['distance'] = '0';
                         $json_creds['amount_per_km'] = '0';
                     }
                     $json_creds = json_encode($json_creds);
                 }
             }

           ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
           $toaster = $this->successToaster(__('Success'), $msg);

         }catch(\Exception $e)
         {
             $toaster = $this->errorToaster(__('Error'), $e->getMessage());
         }

         return redirect()->back()->with('toaster', $toaster);

    }

    //Set new roadie configuration details function
    public function roadie(Request $request)
    {
        try {
            $id = $request->method_id;
            $method_name = strtolower($request->method_name);
            $is_active = $request->active == 'on';
            $is_test_mode = $request->sandbox == 'on';
            $json_creds = NULL;

            if ($is_active) {
                $status = 1;
                $test_mode = $is_test_mode ? 1 : 0;
                if ($method_name == 'roadie') {
                    $validatedData = $request->validate([
                        'api_base_url' => 'required',
                        'api_access_token' => 'required',
                    ]);
                    $json_creds = json_encode([
                        'api_access_token' => $request->api_access_token,
                        'api_base_url' => $request->api_base_url,
                    ]);
                }
            } else {
                $status = 0;
                $test_mode = 0;
            }
            ShippingOption::where('id', $id)->update([
                'status' => $status,
                'credentials' => $json_creds,
                'test_mode' => $test_mode,
            ]);
            $toaster = $this->successToaster(__('Success'), 'Roadie delivery details have been saved successfully!');
        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }
        return redirect()->back()->with('toaster', $toaster);
    }

    //Set new Ahoy(Masa) configuration details function
    public function ahoy(Request $request)
    {

        try {
            //dd($request->input());
            $msg = 'Ahoy delivery details have been saved successfully!';
            $id = $request->method_id;
            $method_name_arr = $request->method_name;
            $active_arr = $request->active;
            $base_active = $request->base_active;
            $test_mode_arr = $request->sandbox;

            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }


                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'ahoy')) {
                    $validatedData = $request->validate([
                        'api_key' => 'required',
                        'app_url' => 'required',
                    ]);
                    $json_creds = array(
                        'api_key' => $request->api_key,
                        'app_url' => (($test_mode == '1') ? 'https://ahoydev.azure-api.net' : 'https://ahoyapis.azure-api.net'),
                    );
                    //dd($json_creds);

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price;
                        $json_creds['distance'] = $request->distance;
                        $json_creds['amount_per_km'] = $request->amount_per_km;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }
                    $json_creds = json_encode($json_creds);
                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);

    }

    //Set new KwikApi configuration details function
    public function updateKwikapi(Request $request)
    {

        try {
            $msg = 'Kwikapi delivery details have been saved successfully!';
            $id = $request->method_id;
            $method_name_arr = $request->method_name;
            $active_arr = $request->active;
            $base_active = $request->base_active;
            $test_mode_arr = $request->sandbox;

            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'kwikapi')) {

                    $validatedData = $request->validate([
                        'kwikapi_pass' => 'required',
                        'domain_name' => 'required',
                        'kwikapi_email' => 'required',
                    ]);

                    $json_creds = array(
                        'api_pass' => $request->kwikapi_pass,
                        'api_email' => $request->kwikapi_email,
                        'domain_name' => $request->domain_name
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price ?? 0;
                        $json_creds['distance'] = $request->distance ?? 0;
                        $json_creds['amount_per_km'] = $request->amount_per_km ?? 0;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }

                    $json_creds = json_encode($json_creds);
                }
            }

            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);

            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);

    }

    //Set new lalamove configuration details function
    public function store(Request $request)
    {
        try {
            //dd($request->input());
            $msg = 'Delivery option have been saved successfully!';
            $id = base64_decode($request->method_id);
            $method_name_arr = $request->input('method_name');
            $active_arr = $request->input('active');
            $base_active = $request->input('base_active');
            $test_mode_arr = $request->input('sandbox');


            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'lalamove')) {
                    $validatedData = $request->validate([
                        'api_key' => 'required',
                        'secret_key' => 'required',
                        'country_key' => 'required',
                        'country_region' => 'required',
                        'locale_key' => 'required',
                        'service_type' => 'required',
                    ]);
                    $json_creds = array(
                        'api_key' => $request->api_key,
                        'secret_key' => $request->secret_key,
                        'country_key' => $request->country_key,
                        'country_region' => $request->country_region,
                        'locale_key' => $request->locale_key,
                        'service_type' => $request->service_type
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price;
                        $json_creds['distance'] = $request->distance;
                        $json_creds['amount_per_km'] = $request->amount_per_km;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }
                    $json_creds = json_encode($json_creds);
                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);
    }

    //Set new ShipEngine configuration details function
    public function updateShipEngine(Request $request)
    {
        try {
            $msg = 'ShipEngine delivery details have been saved successfully!';
            $id = $request->method_id;
            $method_name_arr = $request->method_name;
            $active_arr = $request->active;
            $base_active = $request->base_active;
            $test_mode_arr = $request->sandbox;

            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'shipengine')) {

                    $request->validate([
                        'api_key' => 'required',
                        'service_code' => 'required',
                        'carrier_ids' => 'required',
                    ]);

                    $json_creds = array(
                        'api_key' => $request->api_key,
                        'service_code' => $request->service_code,
                        'carrier_ids' => $request->carrier_ids,
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price ?? 0;
                        $json_creds['distance'] = $request->distance ?? 0;
                        $json_creds['amount_per_km'] = $request->amount_per_km ?? 0;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }

                    $json_creds = json_encode($json_creds);
                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);

            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);

    }

    //Set Last Mile Delivery Configuration Detail
    public function last_mile_delivery(Request $request)
    {
        //  pr($request->all());
        $preferenceset = ClientPreference::where('client_code', Auth::user()->code)->first();
        if (isset($request->need_delivery_service) && !empty($request->need_delivery_service)) {
            try {
                $client = new GClient(['headers' => ['personaltoken' => $request->delivery_service_key, 'shortcode' => $request->delivery_service_key_code, 'content-type' => 'application/json']]);
                $url = $request->delivery_service_key_url;
                $res = $client->post($url . '/api/check-dispatcher-keys');
                $response = json_decode($res->getBody(), true);
                if ($response && $response['status'] == 400) {
                    return redirect()->back()->with('error', 'Last Mile Delivery Keys incorrect !');
                }
            } catch (\Exception $e) {
                return redirect()->back()->with('error', 'Invalid Last Mile Delivery Dispatcher URL !');
            }
            $preferenceset->need_delivery_service = ($request->has('need_delivery_service') && $request->need_delivery_service == 'on') ? 1 : 0;
            $preferenceset->delivery_service_key_url = $request->delivery_service_key_url;
            $preferenceset->delivery_service_key_code = $request->delivery_service_key_code;
            $preferenceset->delivery_service_key = $request->delivery_service_key;
            $preferenceset->last_mile_team = $request->last_mile_team;
        } else {
            $preferenceset->need_delivery_service = ($request->has('need_delivery_service') && $request->need_delivery_service == 'on') ? 1 : 0;
        }
        $preferenceset->save();
        return redirect()->back()->with('success', 'Client configurations updated successfully!');
    }


    public function updateBorzoe(Request $request)
    {
        try {
            $msg = 'Borzoe delivery details have been saved successfully!';
            $id = $request->method_id;
            $method_name_arr = $request->method_name;
            $active_arr = $request->active;
            $base_active = $request->base_active;
            $test_mode_arr = $request->sandbox;

            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr)) && ($active_arr == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr)) && ($test_mode_arr == 'on')) {
                    $test_mode = 1;
                }
                if ((isset($method_name_arr)) && (strtolower($method_name_arr) == 'borzo')) {

                    $request->validate([
                        'api_key' => 'required',
                        'callback_token' => 'required',
                    ]);

                    $json_creds = array(
                        'api_key' => $request->api_key,
                        'service_code' => $request->callback_token,
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price ?? 0;
                        $json_creds['distance'] = $request->distance ?? 0;
                        $json_creds['amount_per_km'] = $request->amount_per_km ?? 0;
                    } else {
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }

                    $json_creds = json_encode($json_creds);
                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);

            $toaster = $this->successToaster(__('Success'), $msg);

        } catch (\Exception $e) {
            $toaster = $this->errorToaster(__('Error'), $e->getMessage());
        }

        return redirect()->back()->with('toaster', $toaster);

    }

}
