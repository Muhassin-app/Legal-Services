<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnToVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->tinyInteger('same_day_delivery')->default(0)->comment('1 for yes, 0 for no');
            $table->tinyInteger('next_day_delivery')->default(0)->comment('1 for yes, 0 for no');
            $table->tinyInteger('hyper_local_delivery')->default(0)->comment('1 for yes, 0 for no');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn(['same_day_delivery',  'next_day_delivery', 'hyper_local_delivery']);
        });
    }
}
