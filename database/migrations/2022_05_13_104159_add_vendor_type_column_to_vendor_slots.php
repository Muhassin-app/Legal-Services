<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorTypeColumnToVendorSlots extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_slots', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_slots', 'slot_type')) {
                $table->tinyInteger('slot_type')->nullable()->default(0)->after('delivery')->comment('0-schedule, 1-pickup, 2-dropoff');
            }
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
            $table->dropColumn('slot_type');
        });
    }
}
