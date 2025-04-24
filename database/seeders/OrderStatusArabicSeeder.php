<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderStatusArabicSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
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
            'Document Reviewed' => 'تمت مراجعة المستند'
        ];

        foreach ($statuses as $title => $arabic_name) {
            DB::table('order_status_options')
                ->where('title', $title)
                ->update(['arabic_name' => $arabic_name]);
        }
    }
} 