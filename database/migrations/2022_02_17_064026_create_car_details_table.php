<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('car_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('car_id')->unsigned()->comment('user address id is car id');
            $table->foreign('car_id')->references('id')->on('user_addresses')->onDelete('cascade');
            $table->string('brand_name')->nullable();
            $table->string('type')->nullable();
            $table->string('vehicle_color')->nullable();
            $table->string('registration_number')->nullable();
           // $table->string('model')->nullable();
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
        Schema::dropIfExists('car_details');
    }
}
