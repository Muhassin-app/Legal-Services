<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveFornkeySmsprovider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $keyExists = DB::select( DB::raw("SHOW KEYS FROM client_preferences WHERE Key_name='sms_provider'") );

        if ($keyExists){
            Schema::table('client_preferences', function (Blueprint $table) {
                $table->dropForeignIfExists(['sms_provider']);
            });
        }


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
