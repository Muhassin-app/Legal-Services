<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bid_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bid_id')->nullable();
            $table->bigInteger('product_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->double('price')->nullable();
            $table->double('total')->nullable();
            $table->timestamps();

            $table->foreign('bid_id')->references('id')->on('bids')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bid_products');
    }
}
