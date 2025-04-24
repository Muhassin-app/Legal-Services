<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddServiceFeeToVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('service_fee_percent', 10, 2)->nullable()->default(0);
        });

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('service_fee_percentage_amount', 10, 2)->nullable()->default(0);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('total_service_fee', 10, 2)->nullable()->default(0);
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
            $table->dropColumn('service_fee_percent');
        });

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropColumn('service_fee_percentage_amount');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_service_fee');
        });
    }
}
