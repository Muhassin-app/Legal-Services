<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceAreaIdToVendorSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_slots', function (Blueprint $table) {
            $table->unsignedBigInteger('service_area_id')->nullable();
        });

        Schema::table('vendor_slot_dates', function (Blueprint $table) {
            $table->unsignedBigInteger('service_area_id')->nullable();
        });

        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('slots_with_service_area')->nullable()->default(0)->comments('0=Active, 1=Inactive');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_slots', function (Blueprint $table) {
            $table->dropColumn('service_area_id');
        });

        Schema::table('vendor_slot_dates', function (Blueprint $table) {
            $table->dropColumn('service_area_id');
        });

        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('slots_with_service_area');
        });
    }
}
