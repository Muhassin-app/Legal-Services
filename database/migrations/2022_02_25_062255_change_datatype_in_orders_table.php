<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatatypeInOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('loyalty_amount_saved', 16, 8)->nullable()->change();
            $table->decimal('total_amount', 16, 8)->nullable()->change();
            $table->decimal('wallet_amount_used', 16, 8)->change();
            $table->decimal('subscription_discount', 16, 8)->nullable()->change();
            $table->decimal('total_discount', 16, 8)->nullable()->change();
            $table->decimal('total_delivery_fee', 16, 8)->nullable()->change();
            $table->decimal('taxable_amount', 16, 8)->nullable()->change();
            $table->decimal('tip_amount', 16, 8)->change();
            $table->decimal('payable_amount', 16, 8)->nullable()->change();
            $table->decimal('total_service_fee', 16, 8)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            //
        });
    }
}
