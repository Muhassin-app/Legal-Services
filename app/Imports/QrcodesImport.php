<?php

namespace App\Imports;

use Maatwebsite\Excel\Row;
use Illuminate\Support\Collection;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\ToCollection;
use App\Models\{Client, CsvQrcodeImport, QrcodeImport};

class QrcodesImport implements ToCollection{
    private $folderName = 'qrcode';

    public function  __construct($vendor_id,$csv_product_import_id){
        $this->vendor_id= $vendor_id;
        $this->csv_product_import_id = $csv_product_import_id;

        // $code = Client::orderBy('id','asc')->value('code');
        // $this->folderName = '/'.$code.'/qrcode';
    }
    public function collection(Collection $rows){
        try {

            $i = 0;
            $data = array();
            $error = array();
            $variant_exist = 0;
            try {


                foreach ($rows as $key =>$row) {
                        
                    if($key == 0)
                        continue;

                    $row = $row->toArray();
                    $checker = 0;
                    if ($row[0] == ""){
                            $error[] = "Row " . $i . " : Code cannot be empty";
                            $checker = 1;
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
                            'code' => $da[0],
                            //'vendor_id'=>$this->vendor_id??Null
                        );

                        if(QrcodeImport::where('code', $da[0])->exists()){
                            QrcodeImport::where('code', $da[0])->update($insert_vendor_details);
                        }else{
                            $newusers[] = $insert_vendor_details;
                        }
                                                
                    }

                    if(isset($newusers) && count($newusers)>0)
                    {
                        QrcodeImport::insert($newusers);
                    }

                }

            } catch(\Exception $ex){
                $error[] = "Other: " .$ex->getMessage();
                \Log::info($ex->getMessage()."".$ex->getLine());
            }

            $vendor_csv = CsvQrcodeImport::where('id', $this->csv_product_import_id)->first();
            if (!empty($error)) {
                $vendor_csv->status = 3;
                $vendor_csv->error = json_encode($error);
            }else{
                $vendor_csv->status = 2;
            }
            $vendor_csv->save();
        } catch(\Exception $ex){
            $error[] = "Other: " .$ex->getMessage();
            \Log::info($ex->getMessage()."".$ex->getLine());
        }
    }

}
