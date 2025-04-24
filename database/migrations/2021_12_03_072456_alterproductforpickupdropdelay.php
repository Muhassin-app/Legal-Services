<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterproductforpickupdropdelay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->Integer('pickup_delay_order_hrs')->default(0);
            $table->Integer('pickup_delay_order_min')->default(0);
            $table->Integer('dropoff_delay_order_hrs')->default(0);
            $table->Integer('dropoff_delay_order_min')->default(0);
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
            $table->dropColumn('pickup_delay_order_hrs');
            $table->dropColumn('pickup_delay_order_min');
            $table->dropColumn('dropoff_delay_order_hrs');
            $table->dropColumn('dropoff_delay_order_min');
        });
    }
}
