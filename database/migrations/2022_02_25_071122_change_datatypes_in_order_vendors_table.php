<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatatypesInOrderVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('delivery_fee', 16, 8)->nullable()->change();
            $table->decimal('taxable_amount', 16, 8)->nullable()->change();
            $table->decimal('subtotal_amount', 16, 8)->nullable()->change();
            $table->decimal('payable_amount', 16, 8)->nullable()->change();
            $table->decimal('discount_amount', 16, 8)->nullable()->change();
            $table->decimal('admin_commission_percentage_amount', 16, 8)->nullable()->change();
            $table->decimal('admin_commission_fixed_amount', 16, 8)->nullable()->change();
            $table->decimal('service_fee_percentage_amount', 16, 8)->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            //
        });
    }
}
