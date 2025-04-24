<?php

namespace Database\Seeders;
use Illuminate\Support\Facades\DB;
use App\Models\OrderStatusOption;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('order_status_options')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $statuses = [
            'Placed' => 'تم الطلب',
            'Accepted' => 'تم القبول',
            'Rejected' => 'مرفوض',
            'Meeting Schedule' => 'جدولة الاجتماع',
            'Document Submitted' => 'تم تقديم المستند',
            'Session Complete' => 'اكتملت الجلسة',
            'Accept' => 'قبول',
            'Reject' => 'رفض',
            'Request Received' => 'تم استلام الطلب',
            'Document Reviewed' => 'تمت مراجعة المستند',
            'Order Prepared' => 'تم تحضير الطلب',
            'Approve Request' => 'الموافقة على الطلب',
            'Schedule Meeting' => 'جدولة الاجتماع',
            'Submit Document' => 'تقديم المستند',
            'Complete Session' => 'إكمال الجلسة'
        ];

        foreach ($statuses as $title => $arabic_name) {
            if(in_array($title, ['Accept', 'Reject'])){
                OrderStatusOption::create([
                    'title' => $title,
                    'arabic_name' => $arabic_name,
                    'status' => 1,
                    'type' => 2
                ]);
            } else {
                OrderStatusOption::create([
                    'title' => $title,
                    'arabic_name' => $arabic_name,
                    'status' => 1,
                    'type' => 1
                ]);
            }
        }
    }
}
