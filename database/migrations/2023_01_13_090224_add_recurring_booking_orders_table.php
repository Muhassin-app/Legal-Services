<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRecurringBookingOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->tinyInteger('recurring_booking_type')->nullable()->comment('1=daily,2=weekly,3=monthly,4=custom');
            $table->string('recurring_week_day')->nullable();
            $table->tinyInteger('recurring_week_type')->nullable()->comment('1=daily,2=once');
            $table->longText('recurring_day_data')->nullable();
            $table->string('recurring_booking_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
            $table->dropColumn('recurring_booking_type');
            $table->dropColumn('recurring_week_day');
            $table->dropColumn('recurring_week_type');
            $table->dropColumn('recurring_day_data');
            $table->dropColumn('recurring_booking_time');
        });
    }
}
