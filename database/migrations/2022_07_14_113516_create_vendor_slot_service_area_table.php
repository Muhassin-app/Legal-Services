<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorSlotServiceAreaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_slot_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_slot_id')->nullable();
            $table->unsignedBigInteger('service_area_id')->nullable();
            $table->timestamps();

            $table->index('vendor_slot_id');
            $table->index('service_area_id');
        });

        Schema::create('vendor_slot_date_service_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_slot_date_id')->nullable();
            $table->unsignedBigInteger('service_area_id')->nullable();
            $table->timestamps();

            $table->index('vendor_slot_date_id');
            $table->index('service_area_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_slot_service_area');
        Schema::dropIfExists('vendor_slot_date_service_area');
    }
}
