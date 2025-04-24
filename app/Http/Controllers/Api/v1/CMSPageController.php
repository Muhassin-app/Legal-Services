<?php

namespace App\Http\Controllers\Api\v1;

use Auth;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;
use App\Http\Controllers\Api\v1\BaseController;
use App\Models\{Client, ClientPreference,FaqTranslations, Page, PageTranslation, VendorRegistrationDocument};

class CMSPageController extends BaseController
{

    use ApiResponser;

    public function getPageList(Request $request)
    {
        // $pages = Page::select('id', 'slug')->with(array('primary' => function($query) {
        //             $query->where('is_published', 1);
        //             }))->latest('id')->get();
        // foreach ($pages as $page) {
        //     $page->title = $page->primary->title;
        //     unset($page->primary);
        // }
        // return $this->successResponse($pages, '', 201);

        $locallanguage = ($request->hasHeader('language')) ? $request->header('language') : 1;
        $pages = Page::leftJoin('page_translations', function ($join) {
            $join->on('pages.id', '=', 'page_translations.page_id');
        })
            ->where(['page_translations.language_id' => $locallanguage, 'page_translations.is_published' => 1])
           // ->orderBy('pages.id', 'Desc')
            ->orderBy('pages.order_by','ASC')->groupBy('pages.id')
            ->get([
                'page_translations.id',
                'pages.slug',
                'page_translations.title',
            ]);
            $pages = $pages->unique('slug')->values()->all();

        return $this->successResponse($pages, '', 201);
    }

    public function getPageDetail(Request $request)
    {
        $data = [];
        $page_id = $request->page_id ? $request->page_id : 3;
        $code = $request->header('code');
        $client = Client::where('code',$code)->first();
        $server_url = "https://".$client->sub_domain.env('SUBMAINDOMAIN')."/";
        $data['terms_and_conditions'] = $server_url . 'page/terms-conditions';
        $data['privacy_policy'] = $server_url . 'page/privacy-policy';

        $getAdditionalPreference = getAdditionalPreference(['is_gst_required_for_vendor_registration', 'is_baking_details_required_for_vendor_registration', 'is_advance_details_required_for_vendor_registration', 'is_vendor_category_required_for_vendor_registration', 'is_seller_module']);

        $data['is_gst_required_for_vendor_registration'] = (int)  $getAdditionalPreference['is_gst_required_for_vendor_registration']??0;
        $data['is_baking_required_for_vendor_registration'] = (int)  $getAdditionalPreference['is_baking_details_required_for_vendor_registration']??0;
        $data['is_advance_details_required_for_vendor_registration'] = (int)  $getAdditionalPreference['is_advance_details_required_for_vendor_registration']??0;
        $data['is_vendor_category_required_for_vendor_registration'] = (int)  $getAdditionalPreference['is_vendor_category_required_for_vendor_registration']??0;
        $data['is_seller_module'] = (int)  $getAdditionalPreference['is_seller_module']??0;
               
        $user = Auth::user();
      //  $langId = $user->language;

        $langId = ($request->hasHeader('language')) ? $request->header('language') : 1;

        $client_preferences = ClientPreference::first();

        $page_detail = Page::with(['translations' => function ($q) use($langId,$page_id) {
            $q->where('language_id', $langId)->where('page_id', $page_id);
        },'translation' => function ($q) use($langId,$page_id) {
            $q->where('language_id', $langId)->where('page_id', $page_id);
        }])->whereHas('translations' , function ($q) use($langId,$page_id) {
            $q->where('language_id', $langId)->where('page_id', $page_id);
        })->whereHas('translation' , function ($q) use($langId,$page_id) {
            $q->where('language_id', $langId)->where('page_id', $page_id);
        })->first();

        $data['page_detail'] = $page_detail;
        $page_detail->primary = $page_detail->translation ?? null;
        if ($page_detail->translation->type_of_form != 2) {
            if($page_detail->primary->type_of_form == 3){
                $faq =   FaqTranslations::where('page_id',$page_detail->id)->where('language_id', $langId)->get();
                $data['faq_data'] = $faq;
            }
            $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
            $data['vendor_registration_documents'] = $vendor_registration_documents;
        }else {
            $driver_types = array(
                ['name' => 'type', 'title' => 'Employee', 'value' => 'Employee'],
                ['name' => 'type', 'title' => 'Freelancer', 'value' => 'Freelancer']
            );
            $transport_types = array(
                ['name' => 'vehicle_type_id', 'type' => 'onfoot', 'value' => 1, 'image'=>$server_url.'assets/icons/walk.png'],
                ['name' => 'vehicle_type_id', 'type' => 'bycycle', 'value' => 2, 'image'=>$server_url.'assets/icons/cycle.png'],
                ['name' => 'vehicle_type_id', 'type' => 'motorbike', 'value' => 3, 'image'=>$server_url.'assets/icons/bike.png'],
                ['name' => 'vehicle_type_id', 'type' => 'car', 'value' => 4, 'image'=>$server_url.'assets/icons/car.png'],
                ['name' => 'vehicle_type_id', 'type' => 'truck', 'value' => 5, 'image'=>$server_url.'assets/icons/truck.png']
            );
            $driverDocs = json_decode($this->driverDocuments(), true);
            foreach ($driverDocs['documents'] as $key => $doc) {
                $name = str_replace(" ", "_", $doc['name']);
                $driverDocs['documents'][$key]['slug'] = $name;
            }
            $data['driver_registration_documents'] = $driverDocs['documents'];
            $data['transport_types'] = $transport_types;
            $data['driver_types'] = $driver_types;
            $data['teams'] = $driverDocs['all_teams'];
            $data['tags'] = $driverDocs['agent_tags'];
            
        }

        return $this->successResponse($data, '', 200);
    }
}
