<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSpotlightDealsInProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('spotlight_deals')->default(0)->nullable()->comment('0-No, 1-Yes');
        });
        Schema::table('home_products', function (Blueprint $table) {
            $table->Integer('layout_id')->nullable()->after('title');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('spotlight_deals');
        });
        Schema::table('home_products', function (Blueprint $table) {
            $table->dropColumn('layout_id');
        });
    }
}
