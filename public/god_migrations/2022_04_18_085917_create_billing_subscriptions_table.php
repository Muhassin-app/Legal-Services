<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingSubscriptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_subscriptions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('client_id');
            $table->integer('billing_price_id');
            $table->string('billing_plan_title', 200);
            $table->string('billing_timeframe_title', 200);
            $table->decimal('billing_price',15, 2);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->date('next_due_date')->nullable();
            $table->integer('billing_payment_id');
            $table->timestamps();
            $table->tinyInteger('status')->comment('(0= >Pending,1=>active, 2=>in active)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_subscriptions');
    }
}
