<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTollTaxRelatedFieldsToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('is_toll_tax')->default(0)->nullable()->comment('0-No, 1-Yes');
            $table->tinyInteger('travel_mode_id')->default(0)->nullable();
            $table->tinyInteger('toll_pass_id')->default(0)->nullable();
            $table->tinyInteger('emission_type_id')->default(0)->nullable();
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
            $table->dropColumn('is_toll_tax');
            $table->dropColumn('travel_mode_id');
            $table->dropColumn('toll_pass_id');
            $table->dropColumn('emission_type_id');
        });
    }
}
