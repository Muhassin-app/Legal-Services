<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdditionalAttributesTranslations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_attributes_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128)->nullable();
            $table->mediumText('slug')->nullable();
            $table->bigInteger('additional_attribute_id')->nullable();
            $table->bigInteger('language_id')->nullable();
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
        Schema::dropIfExists('additional_attributes_translations');
    }
}
