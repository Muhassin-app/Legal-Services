<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableClientPreferenceTaxPriceType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('client_preferences', function (Blueprint $table) {
             //$table->tinyInteger('is_tax_price_inclusive')->default('0'); //No Need Now
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::table('client_preferences', function (Blueprint $table) {
        //     $table->dropColumn('is_tax_price_inclusive');
        // });
    }
}
