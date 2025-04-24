<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShippoDeliveryOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('shippo_delivery_options', function (Blueprint $table) {
            $table->id();
            $table->string('user_id');
            $table->string('vendor_id');
            $table->string('address_id');
            $table->string('zipcode_from');
            $table->string('zipcode_to');
            $table->text('json');
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
        Schema::dropIfExists('shippo_delivery_options');
    }
}
