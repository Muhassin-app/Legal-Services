<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfluencerInitialOrdersDiscountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influencer_initial_orders_discounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('influencer_user_id')->unsigned()->nullable();
            $table->integer('order_count')->comment("number of first orders for discount")->nullable();
            $table->tinyInteger('commision_type')->comment('1=Percentage, 2=fixed')->nullable();
            $table->integer('commision')->comment('commision percentage or amount')->nullable();
            $table->date('date')->nullable();
            $table->timestamps();

            $table->foreign('influencer_user_id')->references('id')->on('influencer_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('influencer_initial_orders_discounts');
    }
}
