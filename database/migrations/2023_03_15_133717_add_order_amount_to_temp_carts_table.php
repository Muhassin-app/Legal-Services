<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderAmountToTempCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_carts', function (Blueprint $table) {
            $table->decimal('order_payable_amount',16,4)->default(0)->nullable();
            $table->decimal('vendor_wallet_amount_used',16,4)->default(0)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_carts', function (Blueprint $table) {
            $table->dropColumn('order_payable_amount');
            $table->dropColumn('vendor_wallet_amount_used');
        });
    }
}
