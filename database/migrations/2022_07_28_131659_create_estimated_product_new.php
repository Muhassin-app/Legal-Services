<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatedProductNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimated_product_new', function (Blueprint $table) {
            $table->id();
            $table->integer('estimated_cart_id')->references('id')->on('estimated_product_cart_new')->onDelete('cascade');
            $table->foreignId('product_id')->references('id')->on('estimate_products')->onDelete('cascade');
            $table->integer('quantity')->nullable();
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
        Schema::dropIfExists('estimated_product_new');
    }
}
