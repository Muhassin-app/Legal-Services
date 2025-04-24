<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProductBooking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product_bookings', function (Blueprint $table) {
            if(!Schema::hasColumn('product_bookings', 'rental_protection_id')){
                $table->integer('rental_protection_id')->nullable();
            }
            if(!Schema::hasColumn('product_bookings', 'booking_option_id')){
                $table->integer('booking_option_id')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product_bookings', function (Blueprint $table) {
            if(Schema::hasColumn('product_bookings', 'rental_protection_id')){
                $table->dropColumn('rental_protection_id');
            }
            if(Schema::hasColumn('product_bookings', 'booking_option_id')){
                $table->dropColumn('booking_option_id');
            }
        });
    }
}
