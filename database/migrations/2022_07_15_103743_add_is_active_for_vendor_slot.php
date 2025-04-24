<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsActiveForVendorSlot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->tinyInteger('is_active_for_vendor_slot')->nullable()->default(0)->comments('0=Inactive, 1=Active');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->tinyInteger('cron_for_service_area')->nullable()->default(0)->comments('0=Inactive, 1=Active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_areas', function (Blueprint $table) {
            $table->dropColumn('is_active_for_vendor_slot');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('cron_for_service_area');
        });
    }
}
