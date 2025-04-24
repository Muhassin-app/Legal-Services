<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorTypeToVendorSlotDatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_slot_dates', function (Blueprint $table) {
            $table->tinyInteger('rental')->nullable()->after('delivery')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('pick_drop')->nullable()->after('rental')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('on_demand')->nullable()->after('pick_drop')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('laundry')->nullable()->after('on_demand')->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('appointment')->nullable()->after('laundry')->default(0)->comment('0-No, 1-Yes');
            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_slot_dates', function (Blueprint $table) {
            //
        });
    }
}
