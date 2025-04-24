<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpdatedPriceColmnToOrderVendorProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_vendor_products', 'old_price'))
            {
              $table->decimal('old_price',16,4)->default(0);
            }
            if (!Schema::hasColumn('order_vendor_products', 'updated_price_reason'))
            {
                 $table->text('updated_price_reason')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendor_products', function (Blueprint $table) {
            //
        });
    }
}
