<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePincodeDeliveryOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pincode_delivery_options', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('pincode_id')->unsigned()->nullable();
            $table->tinyInteger('delivery_option_type')->default(1)->comment('1 for same_day_delivery, 2 for next_day_delivery, 3 for hyper_local_delivery');

            $table->foreign('pincode_id')->references('id')->on('pincodes')->onDelete('cascade');
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
        Schema::dropIfExists('pincode_delivery_options');
    }
}
