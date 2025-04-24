<?php

namespace App\Imports;
use Illuminate\Support\Collection;
use Spatie\Geocoder\Facades\Geocoder;
use App\Models\{CsvCustomerImport, User};
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;

class CustomerImport implements ToCollection
{
    public function  __construct($csv_vendor_import_id){
        $this->csv_vendor_import_id= $csv_vendor_import_id;
    }
    public function collection(Collection $rows){
        try {
            $data = array();
            $error = array();
            $i = 0;
            try {
                foreach ($rows as $key =>$row) {
                        
                    if($key == 0)
                        continue;

                    $row = $row->toArray();
                    $checker = 0;
                    if ($row[0] == ""){
                            $error[] = "Row " . $i . " : Name cannot be empty";
                            $checker = 1;
                        }
                        if($row[1] == "" && $row[2] == ""){
                            if(!is_numeric($row[1])) {
                               // $error[] = "Row " . $i . " : Phone no is required";
                              //  $checker = 1;
                            }
                            continue;
                        }
                        if($row[2] == ""){
                                //$error[] = "Row " . $i . " : E-Mail no is required";
                               // $checker = 1;
                        }
                        
                        if($checker == 0) {
                            $data[] = $row;
                        }
                    
                    $i++;
                }
                if (!empty($data)) {
                    $newusers = [];
                    foreach ($data as $da) {

                        $insert_vendor_details = array(
                            'name' => $da[0],
                            'dial_code' => ($da[1]) ?? 1,
                            'phone_number' => ($da[2]) ?? NULL,
                            'email' => ($da[3]) ?? NULL,
                            'import_user_id' => ($da[4]) ?? NULL,
                            'password' => Hash::make('123456'),
                            'is_email_verified' => ($da[3] == '') ? '0' : '1',
                            'is_phone_verified' => ($da[2] == '') ? '0' : '1',
                            'status'=>'1'
                        );

                        if(User::where('dial_code', $da[1])->where('phone_number', $da[2])->exists()){
                            User::where('dial_code', $da[1])->where('phone_number', $da[2])->update($insert_vendor_details);
                        }
                        elseif(User::where('email',$da[3])->exists()){
                            User::where('email',$da[3])->update($insert_vendor_details);
                        }

                        $user_exists = User::where(function($q) use($da){
                            $q->where('dial_code', $da[1])->where('phone_number', $da[2]);
                        })
                        ->orWhere('email', $da[3])->exists();

                        if (!$user_exists) {
                            $id = User::insertGetId($insert_vendor_details);
                            $user = User::find($id);
                            if(isset($da[5]) && ($da[5] > 0)){
                                $wallet = $user->wallet;
                                $wallet->depositFloat($da[5], ['Wallet has been <b>Credited</b> by Admin']);
                            }   
                        }
                    }

                    if(isset($newusers) && count($newusers)>0)
                    {
                       // User::insert($newusers);
                    }

                }
            } catch(\Exception $ex){
                $error[] = "Other: " .$ex->getMessage();
                \Log::info($ex->getMessage()."".$ex->getLine());
            }
            $csv_vendor_import = CsvCustomerImport::where('id', $this->csv_vendor_import_id)->first();
            if (!empty($error)) {
                $csv_vendor_import->status = 3;
                $csv_vendor_import->error = json_encode($error, true);
            }else{
                $csv_vendor_import->status = 2;
            }
            $csv_vendor_import->save();
        } catch (Exception $e) {
            pr($e->getCode());
        }

    }
}
