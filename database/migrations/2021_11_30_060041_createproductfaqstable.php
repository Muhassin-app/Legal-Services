<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createproductfaqstable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product_faqs', function (Blueprint $table) {
            $table->id();
            $table->integer('is_required')->default(1)->comment('0 means not required, 1 means required');
            $table->timestamps();
        });
        Schema::create('product_faq_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->bigInteger('language_id')->unsigned();
            $table->mediumText('slug')->nullable();
            $table->bigInteger('product_faq_id')->unsigned();
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
        Schema::dropIfExists('product_faqs');
        Schema::dropIfExists('product_faq_translations');
    }
}
