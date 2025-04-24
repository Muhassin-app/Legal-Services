<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createestimateproductstable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimate_products', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
             $table->timestamps();
        });

        Schema::create('estimate_product_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->mediumText('slug')->nullable();
            $table->bigInteger('language_id')->unsigned();
            $table->bigInteger('estimate_product_id')->unsigned();
            $table->timestamps();

            $table->foreign('estimate_product_id')->references('id')->on('estimate_products')->onDelete('cascade');
        });

        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('estimate_products');
        Schema::dropIfExists('estimate_product_translations');
    }
}
