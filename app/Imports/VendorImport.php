<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Spatie\Geocoder\Facades\Geocoder;
use App\Models\{Vendor, CsvVendorImport, UserVendor, VendorRegistrationDocument, VendorDocs, VendorRegistrationSelectOption};
use Maatwebsite\Excel\Concerns\ToCollection;

class VendorImport implements ToCollection
{
    public $roleId;
    public $csv_vendor_import_id;
    
    public function  __construct($csv_vendor_import_id)
    {
        $this->csv_vendor_import_id = $csv_vendor_import_id;
        $this->roleId = (@auth()->user()) ? getRoleId(@auth()->user()->getRoleNames()[0]) : null;
        }

    public function collection(Collection $rows)
    {
        try {
            $vendor_registration_documents = VendorRegistrationDocument::with(['primary', 'options', 'options.translation' => function ($query) {
                $query->where('language_id', session()->get('customerLanguage'));
            }])->get();
            $data = array();
            $error = array();
            $i = 0;

            try {
                foreach ($rows as $row) {
                    $row = $row->toArray();

                    $checker = 0;
                    if ($row[0] != "Upload logo") {

                        if ($row[2] != "") {
                            // $vendor_check = Vendor::where('name', $row[2])->first();
                            // if ($vendor_check) { //if not empty, then is it already exists
                            //     $error[] = "Row " . $i . " : Vendor name already Exist";
                            //     $checker = 1;
                            // }
                        } else {
                            $error[] = "Row " . $i . " : Name cannot be empty";
                            $checker = 1;
                        }
                        if ($row[10] != "") {
                            if (!is_numeric($row[10])) {
                                $error[] = "Row " . $i . " : Invalid input for order prepare time";
                                $checker = 1;
                            }
                        }
                        if ($row[11] != "") {
                            if (!is_numeric($row[11])) {
                                $error[] = "Row " . $i . " : Invalid input for Auto Reject Time";
                                $checker = 1;
                            }
                        }
                        if ($row[12] != "") {
                            if (!is_numeric($row[12])) {
                                $error[] = "Row " . $i . " : Invalid input for Order Minimum Amount";
                                $checker = 1;
                            }
                        }
                        if ($row[14] != "") {
                            if (!is_numeric($row[14])) {
                                $error[] = "Row " . $i . " : Invalid input for Commission Percent";
                                $checker = 1;
                            }
                        }
                        if ($row[15] != "") {
                            if (!is_numeric($row[15])) {
                                $error[] = "Row " . $i . " : Invalid input for Commission Fixed Per Order";
                                $checker = 1;
                            }
                        }
                        if ($row[16] != "") {
                            if (!is_numeric($row[16])) {
                                $error[] = "Row " . $i . " : Invalid input for Commission Monthly";
                                $checker = 1;
                            }
                        }
                        $rownumber = 17;
                        $EasebuzzSubMerchent = EasebuzzSubMerchent();
                        if($EasebuzzSubMerchent ==1 ){
                            $rownumber = 18;
                        }
                        foreach ($vendor_registration_documents as $vendor_registration_document) {

                            if ($vendor_registration_document->file_type == "selector") {
                                if ($vendor_registration_document->is_required == 1) {
                                    if (!array_key_exists($rownumber,$row) || $row[$rownumber] == "") {
                                        $error[] = "Row " . $i . " : " . $vendor_registration_document->primary->slug . " cannot be empty";
                                        $checker = 1;
                                    }
                                }
                                $selectOption = array_key_exists($rownumber,$row) ? $row[$rownumber] : '';
                                $vendorCategoryExists = VendorRegistrationSelectOption::with('translation')
                                    ->whereHas('translation', function ($q) use ($selectOption) {
                                        return $q->select('vendor_registration_select_option_translations.name', 'vendor_registration_select_option_translations.vendor_registration_select_option_id')
                                            ->where('vendor_registration_select_option_translations.name', 'LIKE', $selectOption)
                                            ->groupby('language_id');
                                    })->where('vendor_registration_documents_id', $vendor_registration_document->id)->first();

                                if (!$vendorCategoryExists) { //check if category doesn't exist
                                    $error[] = "Row " . $i . " :" . $vendor_registration_document->primary->slug . " doesn't exist";
                                    $checker = 1;
                                }
                            } else {
                                if ($vendor_registration_document->is_required == 1) {
                                    if ( !array_key_exists($rownumber,$row) || $row[$rownumber] == "") {
                                        $error[] = "Row " . $i . " : " . $vendor_registration_document->primary->slug . " cannot be empty";
                                        $checker = 1;
                                    }
                                }
                                if ( array_key_exists($rownumber,$row)  && $row[$rownumber] != "") {
                                    $imageValidation = array("jpeg", "jpg", "bmp", "png", "JFIF");
                                    $path = parse_url($row[$rownumber], PHP_URL_PATH);
                                    $pathFragments = explode('.', $path);
                                    $end = trim(end($pathFragments));
                                    if ($vendor_registration_document->file_type == "Pdf" && $end != 'pdf') {
                                        $error[] = "Row " . $i . " : " . $vendor_registration_document->primary->slug . " Not a pdf file URL";
                                        $checker = 1;
                                    } elseif ($vendor_registration_document->file_type == "Image"  && (!in_array($end, $imageValidation))) {
                                        $error[] = "Row " . $i . " : " . $vendor_registration_document->primary->slug . " Not a Image file URL";
                                        $checker = 1;
                                    }
                                }
                            }
                            $rownumber++;
                        }

                        if ($checker == 0) {
                            $data[] = $row;
                        }
                    }
                    $i++;
                }

                if (!empty($data)) {
                    $getClientPreferenceDetail = getClientPreferenceDetail();
                    $mapApiKey = $getClientPreferenceDetail ? $getClientPreferenceDetail->map_key : "";
                    foreach ($data as $da) {
                        $latitude = 0;
                        $longitude = 0;
                        if ($da[6] && $mapApiKey) {
                            try{
                                $geoInfo = Geocoder::setApiKey($mapApiKey)->getCoordinatesForAddress($da[6]);
                                $latitude = $geoInfo['lat'] ?? '';
                                $longitude = $geoInfo['lng'] ?? '';
                            }catch (Exception $e) {
                               
                            }
                            
                        }
                        $insert_vendor_details = array(
                            'logo' => ($da[0] == "") ? NULL : trim($da[0]),
                            'banner' => ($da[1] == "") ? NULL : trim($da[1]),
                            'name' => $da[2],
                            'slug' => preg_replace("/[^A-Za-z0-9\-]/", "_", trim(strtolower($da[2]))),
                            'desc' => ($da[3] == "") ? NULL : $da[3],
                            'email' => ($da[4] == "") ? NULL : $da[4],
                            'phone_no' => ($da[5] == "") ? NULL : $da[5],
                            'address' => ($da[6] == "") ? NULL : $da[6],
                            'dine_in' => ($da[7] == 'TRUE') ? 1 : 0,
                            'takeaway' => ($da[8] == 'TRUE') ? 1 : 0,
                            'delivery' => ($da[9] == 'TRUE') ? 1 : 0,
                            'order_pre_time' => ($da[10] == "") ? NULL : $da[10],
                            'auto_reject_time' => ($da[11] == "") ? NULL : $da[11],
                            'order_min_amount' => ($da[12] == "") ? 0.00 : $da[12],
                            'show_slot' => ($da[13] == "TRUE") ? 1 : 0,
                            'commission_percent' => ($da[14] == "") ? NULL : $da[14],
                            'commission_fixed_per_order' => ($da[15] == "") ? NULL : $da[15],
                            'commission_monthly' => ($da[16] == "") ? NULL : $da[16],
                            'latitude' => $latitude,
                            'longitude' => $longitude,
                        );

                        if(@$this->roleId=='5')
                        {
                            $insert_vendor_details['refference_id'] = auth()->id()??null;
                        }

                        $vendorID  =  Vendor::insertGetId($insert_vendor_details);
                        $vendorData = Vendor::where('id', $vendorID)->first();

                        if(@$this->roleId=='5')
                        {
                            UserVendor::updateOrCreate(['user_id' =>  auth()->id(),'vendor_id' => $vendorID]);
                        }

                        $daKey = 17;                    
                        $EasebuzzSubMerchent = EasebuzzSubMerchent();
                        if($EasebuzzSubMerchent ==1 ){
                            $vendorData->easebuzz_sub_merchent_id = ($da[17] == "") ? NULL : $da[17];
                            $vendorData->save();
                            $daKey = 18;
                        }
                       
                        $vendor_registration_documents = VendorRegistrationDocument::with('primary')->get();
                        if ($vendor_registration_documents->count() > 0) {
                            foreach ($vendor_registration_documents as $vendor_registration_document) {

                                if ($vendor_registration_document->file_type == "selector") {

                                    if ( isset($da[$daKey]) && $da[$daKey] != "") {
                                        $saveselectOption = $da[$daKey];
                                        $vendorOptionExists = VendorRegistrationSelectOption::with('translation')
                                            ->whereHas('translation', function ($q) use ($saveselectOption) {
                                                return $q->select('vendor_registration_select_option_translations.name', 'vendor_registration_select_option_translations.vendor_registration_select_option_id')
                                                    ->where('vendor_registration_select_option_translations.name', 'LIKE', $saveselectOption)
                                                    ->groupby('language_id');
                                            })->where('vendor_registration_documents_id', $vendor_registration_document->id)->first();

                                        if ($vendorOptionExists) {
                                            $vendor_docs =  new VendorDocs();
                                            $vendor_docs->vendor_id = $vendorID;
                                            $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                                            $vendor_docs->file_name = $vendorOptionExists->id;
                                            $vendor_docs->save();
                                        }
                                    }
                                } else {
                                    if (isset($da[$daKey]) && $da[$daKey] != "") {
                                        $vendor_docs =  new VendorDocs();
                                        $vendor_docs->vendor_id = $vendorID;
                                        $vendor_docs->vendor_registration_document_id = $vendor_registration_document->id;
                                        $vendor_docs->file_name = trim($da[$daKey]);
                                        $vendor_docs->save();
                                    }
                                }
                                $daKey++;
                            }
                        }
                    }
                   
                    $vendorData->document = VendorDocs::where('vendor_id', $vendorID)->get()->toArray();
                }
            } catch (\Exception $ex) {
                $error[] = "Other: " . $ex->getMessage();
                \Log::info($ex->getMessage() . "" . $ex->getLine());
            }
            $csv_vendor_import = CsvVendorImport::where('id', $this->csv_vendor_import_id)->first();
            if (!empty($error)) {
                //pr($error);
                $csv_vendor_import->status = 3;
                $csv_vendor_import->error = json_encode($error, true);
            } else {
                $csv_vendor_import->status = 2;
            }
            $csv_vendor_import->save();
        } catch (Exception $e) {
            pr($e->getCode());
        }
    }
}
