<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_payment_details', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('client_id');
            $table->integer('billing_subscription_id');
            $table->integer('total_amount');
            $table->integer('paid_amount');
            $table->integer('remaining_amount');
            $table->integer('paid_status')->comment('(0=>not paid, 1=fully paid, 2=>partial paid)');
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
        Schema::dropIfExists('billing_payment_details');
    }
}
