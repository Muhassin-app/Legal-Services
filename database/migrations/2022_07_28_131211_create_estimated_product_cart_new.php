<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatedProductCartNew extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimated_product_cart_new', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('item_count')->nullable();
            $table->foreignId('currency_id')->nullable();
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
        Schema::dropIfExists('estimated_product_cart_new');
    }
}
