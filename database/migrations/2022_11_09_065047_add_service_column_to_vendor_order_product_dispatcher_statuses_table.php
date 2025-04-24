<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceColumnToVendorOrderProductDispatcherStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_order_product_dispatcher_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('order_product_route_id')->nullable()->change();
            $table->unsignedBigInteger('long_term_schedule_id')->nullable()->comment('long_term_schedule_id from order_long_term_service_schedules');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_order_product_dispatcher_statuses', function (Blueprint $table) {
            $table->dropColumn('long_term_schedule_id'); 
        });
    }
}
