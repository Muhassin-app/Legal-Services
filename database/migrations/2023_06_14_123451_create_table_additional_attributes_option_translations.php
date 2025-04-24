<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdditionalAttributesOptionTranslations extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_attributes_option_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 128)->nullable();
            $table->bigInteger('additional_attribute_option_id')->nullable();
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
        Schema::dropIfExists('additional_attributes_option_translations');
    }
}
