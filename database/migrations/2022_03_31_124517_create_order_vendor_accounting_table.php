<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderVendorAccountingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_vendor_accounting', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_vendor_id')->unsigned();
            $table->integer('third_party_accounting_id')->unsigned(); 
            $table->string('invoice_id');
            $table->timestamps(); 
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_vendor_accounting');
    }
}
