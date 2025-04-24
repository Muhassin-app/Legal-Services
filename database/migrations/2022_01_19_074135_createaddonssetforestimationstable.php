<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Createaddonssetforestimationstable extends Migration
{ 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::create('estimate_addon_sets', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->tinyInteger('min_select')->default(1);
            $table->tinyInteger('max_select')->default(1);
            $table->smallInteger('position')->default(1);
            $table->tinyInteger('status')->default('1')->comment('0 - pending, 1 - active, 2 - blocked');
            $table->tinyInteger('is_core')->default('1')->comment('0 - no, 1 - yes');
            $table->timestamps();

        });

        Schema::create('estimate_addon_set_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->bigInteger('estimate_addon_id')->unsigned()->nullable();
            $table->bigInteger('language_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('estimate_addon_id')->references('id')->on('estimate_addon_sets')->onDelete('cascade');
            $table->foreign('language_id')->references('language_id')->on('client_languages')->onDelete('cascade');
        });

        Schema::create('estimate_addon_options', function (Blueprint $table) {
            $table->id();
            $table->string('title', 50)->nullable();
            $table->bigInteger('estimate_addon_id')->unsigned();
            $table->smallInteger('position')->default(1);
            $table->decimal('price', 10, 2)->nullable();
            $table->timestamps();

            $table->foreign('estimate_addon_id')->references('id')->on('estimate_addon_sets')->onDelete('cascade');
        });

        Schema::create('estimate_addon_option_translations', function (Blueprint $table) {
            $table->id();
            $table->string('title', 100)->nullable();
            $table->bigInteger('estimate_addon_opt_id')->unsigned()->nullable();
            $table->bigInteger('language_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('estimate_addon_opt_id')->references('id')->on('estimate_addon_options')->onDelete('cascade');
            $table->foreign('language_id')->references('language_id')->on('client_languages')->onDelete('cascade');
        });

        Schema::create('estimate_product_addons', function (Blueprint $table) {
            $table->bigInteger('estimate_product_id')->unsigned()->nullable();
            $table->bigInteger('estimate_addon_id')->unsigned()->nullable();
            $table->timestamps();

            $table->foreign('estimate_product_id')->references('id')->on('estimate_products')->onDelete('cascade');
            $table->foreign('estimate_addon_id')->references('id')->on('estimate_addon_sets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {   
        Schema::dropIfExists('estimate_addon_sets');
        Schema::dropIfExists('estimate_addon_set_translations');
        Schema::dropIfExists('estimate_addon_options');
        Schema::dropIfExists('estimate_addon_option_translations');
        Schema::dropIfExists('estimate_product_addons');
    }
}
