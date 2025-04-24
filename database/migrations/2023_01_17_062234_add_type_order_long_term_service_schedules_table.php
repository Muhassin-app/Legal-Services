<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeOrderLongTermServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_long_term_service_schedules', function (Blueprint $table) {
            //
            $table->tinyInteger('type')->nullable()->comment('1=Longterm Service,2=Recurring Service');
            $table->bigInteger('order_vendor_product_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_long_term_service_schedules', function (Blueprint $table) {
            //
            $table->dropColumn('type');
            $table->dropColumn('order_vendor_product_id');

        });
    }
}
