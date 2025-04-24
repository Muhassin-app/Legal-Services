<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AlterDeliveryMinAndDeliverMaxExactNumberToVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
     {
         Schema::table('vendors', function (Blueprint $table) {
           DB::statement("ALTER TABLE `vendors` CHANGE `delivery_fee_minimum` `delivery_fee_minimum` DECIMAL(64,2) NOT NULL DEFAULT '0.00', CHANGE `delivery_fee_maximum` `delivery_fee_maximum` DECIMAL(64,2) NOT NULL DEFAULT '0.00'");
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
           DB::statement("ALTER TABLE `vendors` CHANGE `delivery_fee_minimum` `delivery_fee_minimum` DECIMAL(64) NOT NULL DEFAULT '0.00', CHANGE `delivery_fee_maximum` `delivery_fee_maximum` DECIMAL(64) NOT NULL DEFAULT '0'");
         });
     }
}
