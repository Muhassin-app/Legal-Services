<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCartRentalProtection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!checkTableExists('cart_rental_protections')) {
            Schema::create('cart_rental_protections', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('rental_protection_id');
                $table->integer('cart_id');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_rental_protections');
    }
}
