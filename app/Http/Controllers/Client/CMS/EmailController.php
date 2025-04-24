<?php

namespace App\Http\Controllers\Client\CMS;
use Illuminate\Http\Request;
use App\Models\EmailTemplate;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller{
    use ApiResponser;
    public function index(){
        $email_templates = EmailTemplate::all();
        return view('backend.cms.email.index', compact('email_templates'));
    }
    public function show(Request $request, $domain = '', $id){
        $email_template =  EmailTemplate::where('id', $id)->first();
        return $this->successResponse($email_template);
    }
    public function update(Request $request, $id){
        $rules = array(
            'subject' => 'required',
            'content' => 'required',
        );
        $validation  = Validator::make($request->all(), $rules)->validate();
        $email_template = EmailTemplate::where('id', $request->email_template_id)->firstOrFail();
        $email_template->subject = $request->subject;
        $email_template->content = $request->content;
        $email_template->save();
        return $this->successResponse($email_template, 'Email Template Updated Successfully.');
    }
}
