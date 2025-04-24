<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Altercabbookinglayoutsectionfornoproduct extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
     
        Schema::table('cab_booking_layouts', function (Blueprint $table) {
            $table->tinyInteger('for_no_product_found_html')->nullable()->default(0)->comment('0-No, 1-Yes');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cab_booking_layouts', function (Blueprint $table) {
            $table->dropColumn('for_no_product_found_html');
        });
    }
}
