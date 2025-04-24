<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderVendorProductIdToVendorOrderProductStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_order_product_statuses', function (Blueprint $table) {
            $table->bigInteger('order_vendor_product_id')->unsigned()->nullable();

            $table->foreign('order_vendor_product_id')->references('id')->on('order_vendor_products')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_order_product_statuses', function (Blueprint $table) {
            $table->dropColumn('order_vendor_product_id');
        });
    }
}
