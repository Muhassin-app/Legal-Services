<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderRefundsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_refunds', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders')->onDelete('cascade');
            $table->bigInteger('payment_id')->unsigned();
            $table->bigInteger('payment_option_id')->unsigned();
            $table->foreign('payment_option_id')->references('id')->on('payment_options')->onDelete('cascade');
            $table->string('transaction_id')->nullable();
            $table->decimal('amount',12,2)->unsigned()->default(0.00);
            $table->longText('webhook_payload')->nullable();
            $table->tinyInteger('paid_to_wallet')->default(0)->comment('0=no 1=yes');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('order_refunds');
    }
}
