<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPaymentTransationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_payment_transactions', function (Blueprint $table) {
            $table->integer('id');
            $table->integer('billing_payment_id');
            $table->tinyInteger('payment_type')->comment('(1=>Manual, 2=>automatic, 3=>by client)');
            $table->tinyInteger('payment_mode')->comment('(1=>Card,2=>Net Banking,3=>Wallet)');
            $table->string('transaction_id', 100);
            $table->string('receipt', 500);
            $table->decimal('paid_amount', 12);
            $table->date('payment_date')->nullable();
            $table->tinyInteger('status')->comment('(1=>Success,2=>Failure) Depends on What status we are receiving');
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
        Schema::dropIfExists('billing_payment_transations');
    }
}
