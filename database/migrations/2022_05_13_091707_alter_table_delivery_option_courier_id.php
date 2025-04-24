<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableDeliveryOptionCourierId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `order_vendors` MODIFY `courier_id` VARCHAR(50);");
        DB::statement("ALTER TABLE `cart_vendor_delivery_fee` MODIFY `courier_id` VARCHAR(50);");
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
