<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLongTermServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_long_term_services', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('order_product_id');
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('service_quentity')->unsigned()->nullable();
            $table->string('service_day',150)->nullable();
            $table->string('service_date',150)->nullable();
            $table->string('service_period',150)->nullable();
            $table->dateTime('service_start_date')->nullable();
            $table->dateTime('service_end_date')->nullable();
            $table->unsignedBigInteger('service_product_id');
            $table->unsignedBigInteger('service_product_variant_id');
            $table->tinyInteger('status')->default(0)->comment('0-not accept, 1-accept');
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
        Schema::dropIfExists('order_long_term_services');
    }
}
