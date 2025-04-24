<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Traits\ToasterResponser;
use App\Http\Controllers\Client\BaseController;
use App\Models\{VerificationOption};
use Auth, Storage, Validator;

class VerificationController extends Controller
{
    use ToasterResponser;

    public function index() 
    {
        $verify_codes = array('passbase');
        $verify_options = VerificationOption::whereIn('code', $verify_codes)->get();
        return view('backend.verify_option.index')->with(['verify_options' => $verify_options]); 
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
        $msg = 'Verification options have been saved successfully!';
        $method_id_arr = $request->input('method_id');
        $method_name_arr = $request->input('method_name');
        $active_arr = $request->input('active');
        $test_mode_arr = $request->input('sandbox');

        foreach ($method_id_arr as $key => $id) {
            $saved_creds = VerificationOption::select('credentials')->where('id', $id)->first();
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

                if ((isset($method_name_arr[$key])) && (strtolower($method_name_arr[$key]) == 'passbase')) {
                    $validatedData = $request->validate([
                        'passbase_publish_key'  => 'required',
                        'passbase_secret_key'   => 'required',
                    ]);
                    $json_creds = json_encode(array(
                        'publish_key' => $request->passbase_publish_key,
                        'secret_key'  => $request->passbase_secret_key,
                    ));
                }
                
            }
            VerificationOption::where('id', $id)->update(['status' => $status, 'credentials' => $json_creds, 'test_mode' => $test_mode]);
        }
        $toaster = $this->successToaster(__('Success'), $msg);
        return redirect()->back()->with('toaster', $toaster);
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
}
