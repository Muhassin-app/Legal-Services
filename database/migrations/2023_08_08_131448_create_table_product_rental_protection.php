<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductRentalProtection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!checkTableExists('product_rental_protections')) {
            Schema::create('product_rental_protections', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('rental_proctection_id');
                $table->integer('type_id')->comment('1 = included, 2 = not included in product price');
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
        Schema::dropIfExists('product_rental_protections');
    }
}
