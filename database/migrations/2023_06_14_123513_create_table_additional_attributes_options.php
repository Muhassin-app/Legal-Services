<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdditionalAttributesOptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_attributes_options', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128)->nullable();
            $table->bigInteger('additional_attribute_id')->nullable();
            $table->string('hexcode', 10)->nullable();
            $table->smallInteger('position')->nullable();
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
        Schema::dropIfExists('additional_attributes_options');
    }
}
