<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExchangeOrderColumnsToOrderVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->tinyInteger('is_exchanged_or_returned')->default(0)->comment('1 = exchanged, 2 = returned');
            $table->bigInteger('exchange_order_vendor_id')->unsigned()->nullable();

            $table->foreign('exchange_order_vendor_id')->references('id')->on('order_vendors')->onUpdate('cascade')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            //
        });
    }
}
