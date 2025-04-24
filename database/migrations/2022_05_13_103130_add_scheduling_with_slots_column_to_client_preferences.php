<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSchedulingWithSlotsColumnToClientPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('client_preferences', 'scheduling_with_slots')) {
                $table->tinyInteger('scheduling_with_slots')->nullable()->default(0)->comment('0-No, 1-Yes');
                $table->tinyInteger('same_day_delivery_for_schedule')->nullable()->default(0)->comment('0-off, 1-on');
                $table->tinyInteger('same_day_orders_for_rescheduing')->nullable()->default(0)->comment('0-off, 1-on');
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
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('scheduling_with_slots');
            $table->dropColumn('same_day_delivery_for_schedule');
            $table->dropColumn('same_day_orders_for_rescheduing');
        });
    }
}
