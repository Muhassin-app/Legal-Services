<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatusToVendorOrderProductDispatcherStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_order_product_dispatcher_statuses', function (Blueprint $table) {
            $table->string('order_status_option_id')->nullable()->comment('single product dispatch');
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
            //
        });
    }
}
