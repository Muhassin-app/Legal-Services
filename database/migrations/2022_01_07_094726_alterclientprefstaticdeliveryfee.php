<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterclientprefstaticdeliveryfee extends Migration
{
     /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('static_delivey_fee')->default(0)->comment('0-No, 1-Yes');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('order_amount_for_delivery_fee', 64, 0)->default(0);
            $table->decimal('delivery_fee_minimum', 64, 0)->default(0);
            $table->decimal('delivery_fee_maximum', 64, 0)->default(0);
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
            $table->dropColumn('static_delivey_fee');
        });

        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('order_amount_for_delivery_fee');
            $table->dropColumn('delivery_fee_minimum');
            $table->dropColumn('delivery_fee_maximum');
        });
        
        
    }
}
