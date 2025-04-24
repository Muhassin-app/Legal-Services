<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNoSeatsForPoolingAndIsCabPoolingToOrderVendorProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            $table->tinyInteger('no_seats_for_pooling')->default(0)->nullable();
            $table->tinyInteger('is_cab_pooling')->default(0)->nullable()->comment('0-No, 1-Yes');
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
            $table->dropColumn('no_seats_for_pooling');
            $table->dropColumn('is_cab_pooling');
        });
    }
}
