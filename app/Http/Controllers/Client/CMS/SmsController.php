<?php

namespace App\Http\Controllers\Client\CMS;
use Illuminate\Http\Request;
use App\Models\SmsTemplate;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class SmsController extends Controller
{
    use ApiResponser;

    public function index(){
        $sms_templates = SmsTemplate::all();
        return view('backend.cms.sms.index', compact('sms_templates'));
    }
    public function show(Request $request, $domain = '', $id){
        $sms_template =  SmsTemplate::where('id', $id)->first();
        return $this->successResponse($sms_template);
    }
    public function update(Request $request, $id){
        $rules = array(
            'subject' => 'required',
            'content' => 'required',
        );
        $validation  = Validator::make($request->all(), $rules)->validate();
        $sms_template = SmsTemplate::where('id', $request->email_template_id)->firstOrFail();
        $sms_template->subject = $request->subject;
        $sms_template->content = $request->content;
        $sms_template->template_id = $request->template_id;
        $sms_template->save();
        return $this->successResponse($sms_template, 'Sms Template Updated Successfully.');
    }
}
