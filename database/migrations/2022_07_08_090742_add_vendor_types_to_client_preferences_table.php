<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorTypesToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('rental_check')->nullable()->after('delivery_check')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('pick_drop_check')->nullable()->after('rental_check')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('on_demand_check')->nullable()->after('pick_drop_check')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('laundry_check')->nullable()->after('on_demand_check')->default(0)->comment('0-No, 1-Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            //
        });
    }
}
