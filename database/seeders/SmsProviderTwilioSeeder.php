<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ClientPreference;
use DB;

class SmsProviderTwilioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $update = ClientPreference::where('id',1)->update(['sms_provider' => 1]);
    }
}
