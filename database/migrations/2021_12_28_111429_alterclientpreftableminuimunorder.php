<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterclientpreftableminuimunorder extends Migration
{ 
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('minimum_order_batch')->nullable()->default(0)->comment('0-No, 1-Yes');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->Integer('minimum_order_count')->default(1);
            $table->Integer('batch_count')->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('minimum_order_batch');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('minimum_order_count');
            $table->dropColumn('batch_count');
        });
    }
}
