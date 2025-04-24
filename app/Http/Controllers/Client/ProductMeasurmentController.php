<?php

namespace App\Http\Controllers\Client;

use App\Models\Category;
use App\Models\Measurements;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class ProductMeasurmentController extends Controller
{
    //
    private $vendorObj;
    public function __construct()
    {

    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories=Category::all();
        return view('backend.measurement.index')->with(['categories'=>$categories]);

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


    public function storeData(Request $request)
    {
        try {
            $rule = array(
                'category_id' => 'required',
                'field_type' => 'required',
                'key_name' => 'required',
                'vendor_id' => 'required'
            );
            $validation  = Validator::make($request->all(), $rule);
            if ($validation->fails()) {
                return redirect()->back()->withInput()->withErrors($validation);
            }

            $measurementsExists=Measurements::where('category_id',$request->category_id)->where('vendor_id',$request->vendor_id)->where('key',$request->key_name)->first();
            if(!empty($measurementsExists)){
                return redirect()->back()->with('error', 'This Measurement  already exists');

            }
            $measurements= new Measurements();
            $measurements->key = $request->key_name;
            $measurements->category_id = $request->category_id;
            $measurements->field_type = $request->field_type;
            $measurements->vendor_id = $request->vendor_id;
            $measurements->save();
            return redirect()->back()->with('success', 'Measurement data saved successfully!');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store()
    {
        try {
            

        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
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