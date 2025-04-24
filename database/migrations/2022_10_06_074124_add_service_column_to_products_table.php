<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceColumnToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('is_long_term_service')->nullable()->default(0)->comment('0-No, 1-Yes');
            //$table->string('service_period',150)->nullable()->comment('day,week,month');
            $table->bigInteger('service_duration')->nullable()->default(0)->comment('long term servier Months');
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
            $table->dropColumn('is_long_term_service');
            $table->dropColumn('service_duration');
        });
    }
}
