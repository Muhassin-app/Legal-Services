<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDeliveryDurationCartDeliveryFeeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_vendor_delivery_fee', function (Blueprint $table) {
            $table->tinyInteger('delivery_duration')->nullable()->after('delivery_fee');
            $table->decimal('delivery_distance', 16, 2)->nullable()->after('delivery_duration');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_vendor_delivery_fee', function (Blueprint $table) {

        });
    }
}
