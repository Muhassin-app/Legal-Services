<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTrackingOrderPhoneTokenUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->string('track_order_phone_token', 20)->nullable()->after('phone_token_valid_till');
            $table->timestamp('track_order_phone_token_valid_till')->nullable()->after('track_order_phone_token');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
            $table->dropColumn('track_order_phone_token');
            $table->dropColumn('track_order_phone_token_valid_till');
        });
    }
}
