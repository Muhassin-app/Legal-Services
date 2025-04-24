<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductDispatchRoutesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_product_dispatch_routes', function (Blueprint $table) {
            $table->id()->comment('id based on product quantity');
            $table->unsignedBigInteger('order_id');
            $table->unsignedBigInteger('order_vendor_id');
            $table->unsignedBigInteger('order_vendor_product_id');
            $table->unsignedBigInteger('order_product_route_id');
            $table->string('web_hook_code')->nullable()->comment('single product dispatch');
            $table->string('dispatch_traking_url')->nullable()->comment('single product dispatch');
            $table->string('dispatcher_status_option_id')->nullable()->comment('single product dispatch');
            $table->string('order_status_option_id')->nullable()->comment('single product dispatch');
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
        Schema::dropIfExists('order_product_dispatch_routes');
    }
}
