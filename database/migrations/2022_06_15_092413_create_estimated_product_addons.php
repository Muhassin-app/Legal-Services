<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEstimatedProductAddons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('estimated_product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estimated_product_id')->references('id')->on('estimated_products')->onDelete('cascade');
            $table->foreignId('estimated_addon_id')->references('id')->on('estimate_addon_sets')->onDelete('cascade');
            $table->foreignId('estimated_addon_option_id')->references('id')->on('estimate_addon_options')->onDelete('cascade');
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
        Schema::dropIfExists('estimated_product_addons');
    }
}
