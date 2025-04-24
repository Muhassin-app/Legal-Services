<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceAreaForBannersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_area_for_banners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 60)->index();
            $table->text('description')->nullable();
            $table->text('geo_array')->nullable();
            $table->smallInteger('zoom_level')->default(13);
            $table->geometry('polygon')->nullable();
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
        Schema::dropIfExists('service_area_for_banners');
    }
}
