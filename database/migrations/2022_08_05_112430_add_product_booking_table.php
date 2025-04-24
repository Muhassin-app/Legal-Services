<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductBookingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('product_id');
            $table->integer('order_user_id')->nullable();
            $table->integer('order_vendor_id')->nullable()->nullable();
            $table->integer('variant_id')->nullable();
            $table->longText('memo')->nullable();
            $table->enum('booking_type', ['blocked', 'new_booking']);
            $table->dateTime('start_date_time')->nullable();
            $table->dateTime('end_date_time')->nullable();
            $table->string('booking_start_end')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('product_bookings');
    }
}
