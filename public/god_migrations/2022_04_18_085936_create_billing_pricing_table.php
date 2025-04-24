<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPricingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_pricing', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('slug', 150);
            $table->integer('billing_plan_id');
            $table->integer('billing_timeframe_id');
            $table->decimal('price', 12);
            $table->decimal('old_price', 12);
            $table->timestamps();
            $table->tinyInteger('status')->comment('(0=>Pending,1=>active, 2=>in active)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_pricing');
    }
}
