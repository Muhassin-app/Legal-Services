<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBidIdCartProductsAndOrderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->integer('bid_number')->nullable();
            $table->integer('bid_discount')->nullable();
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('bid_number')->nullable();
            $table->integer('bid_discount')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropColumn('bid_discount');
            $table->dropColumn('bid_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('bid_number');
            $table->dropColumn('bid_discount');
        });
    }
}
