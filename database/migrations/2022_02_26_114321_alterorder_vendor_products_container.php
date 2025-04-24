<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterorderVendorProductsContainer extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->decimal('container_charges', 12, 4)->nullable()->default(0);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_container_charges', 12, 4)->nullable()->default(0);
        });
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('total_container_charges', 12, 4)->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->dropColumn('container_charges');
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_container_charges');
        });
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropColumn('total_container_charges');
        });
    }
}
