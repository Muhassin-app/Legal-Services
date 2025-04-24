<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorOrderCancelReturnPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_order_cancel_return_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('order_vendor_id')->nullable();
            $table->string('wallet_amount')->nullable();
            $table->string('online_payment_amount')->nullable();
            $table->string('loyalty_amount')->nullable();
            $table->string('loyalty_points')->nullable();
            $table->string('loyalty_points_earned')->nullable();
            $table->string('total_return_amount')->nullable();
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
        Schema::dropIfExists('vendor_order_cancel_return_payments');
    }
}
