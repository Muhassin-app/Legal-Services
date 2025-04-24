<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ToasterResponser;
use App\Http\Controllers\Client\BaseController;
use App\Models\{Client, ClientPreference,ShippingOption};
use Auth, Storage, Validator;

class ShippingOptionController extends BaseController
{
    use ToasterResponser;
    private $folderName;

    public function __construct()
    {
        $code = Client::orderBy('id', 'asc')->value('code');
        $this->folderName = '/' . $code . '/shipoption';
    }
    public function index()
    {
        $shipping_codes = ['shiprocket'];
        $shipingOption = ShippingOption::whereIn('code', $shipping_codes)->get();
        return view('backend/shipoption/index')->with(['shipingOption' => $shipingOption]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function updateAll(Request $request, $domain = '')
    {
        $msg = 'Shipping options have been saved successfully!';
        $method_id_arr = $request->input('method_id');
        $method_name_arr = $request->input('method_name');
        $active_arr = $request->input('active');
        $base_active = $request->input('base_active');
        $test_mode_arr = $request->input('sandbox');

        foreach ($method_id_arr as $key => $id) {
            $saved_creds = ShippingOption::select('credentials')->where('id', $id)->first();
            if ((isset($saved_creds)) && (!empty($saved_creds->credentials))) {
                $json_creds = $saved_creds->credentials;
            } else {
                $json_creds = NULL;
            }

            $status = 0;
            $test_mode = 0;
            if ((isset($active_arr[$id])) && ($active_arr[$id] == 'on')) {
                $status = 1;

                if ((isset($test_mode_arr[$id])) && ($test_mode_arr[$id] == 'on')) {
                    $test_mode = 1;
                }

                if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'shiprocket')) {
                    $validatedData = $request->validate([
                        'shiprocket_username'       => 'required',
                        'shiprocket_password'       => 'required',
                    ]);
                    $json_creds = array(
                        'username' => $request->shiprocket_username,
                        'password' => $request->shiprocket_password,
                    );

                    if ((isset($base_active)) && ($base_active == 'on')) {
                        $json_creds['base_price'] = $request->base_price;
                        $json_creds['distance'] = $request->distance;
                        $json_creds['amount_per_km'] = $request->amount_per_km;
                    }else{
                        $json_creds['base_price'] = '0';
                        $json_creds['distance'] = '0';
                        $json_creds['amount_per_km'] = '0';
                    }
                        $json_creds['height'] = ($request->height) ?? 0;
                        $json_creds['width'] = ($request->width) ?? 0;
                        $json_creds['weight'] = ($request->weight) ?? 0;

                    $json_creds = json_encode($json_creds);

                }
            }
            ShippingOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
        }
        $toaster = $this->successToaster(__('Success'), $msg);
        return redirect()->back()->with('toaster', $toaster);
    }
}
