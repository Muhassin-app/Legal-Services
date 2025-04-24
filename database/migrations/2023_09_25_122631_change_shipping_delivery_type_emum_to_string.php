<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeShippingDeliveryTypeEmumToString extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table){
            $table->string('shipping_delivery_type')->change()->default('D');
        });

        Schema::table('carts', function (Blueprint $table) {
            $table->string('shipping_delivery_type')->change()->default('D');
        });

        Schema::table('cart_vendor_delivery_fee', function (Blueprint $table) {
            $table->string('shipping_delivery_type')->change()->default('D');
        });

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->string('shipping_delivery_type')->change()->default('D');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
