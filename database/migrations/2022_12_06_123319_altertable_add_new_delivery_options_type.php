<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AltertableAddNewDeliveryOptionsType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("ALTER TABLE `cart_vendor_delivery_fee` CHANGE `shipping_delivery_type` `shipping_delivery_type` ENUM('D', 'L', 'S','SR','DU','M','SH','SHF','K') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D';");
        DB::statement("ALTER TABLE `carts` CHANGE `shipping_delivery_type` `shipping_delivery_type` ENUM('D', 'L', 'S','SR','DU','M','SH','SHF','K') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D';");
        DB::statement("ALTER TABLE `orders` CHANGE `shipping_delivery_type` `shipping_delivery_type` ENUM('D', 'L', 'S','SR','DU','M','SH','SHF','K') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D';");
        DB::statement("ALTER TABLE `order_vendors` CHANGE `shipping_delivery_type` `shipping_delivery_type` ENUM('D', 'L', 'S','SR','DU','M','SH','SHF','K') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D';");
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
