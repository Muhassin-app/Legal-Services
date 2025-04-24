<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProductBookingOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!checkTableExists('product_booking_options')) {
            Schema::create('product_booking_options', function (Blueprint $table) {
                $table->id();
                $table->integer('product_id');
                $table->integer('booking_option_id');
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
        Schema::dropIfExists('product_booking_options');
    }
}
