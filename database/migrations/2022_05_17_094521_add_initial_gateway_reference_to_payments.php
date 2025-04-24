<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInitialGatewayReferenceToPayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('orders', 'initial_gateway_reference')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('initial_gateway_reference'); //drop it
            });
        }

        if (Schema::hasColumn('orders', 'order_reference_for_gateway')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('order_reference_for_gateway'); //drop it
            });
        }

        Schema::table('payments', function (Blueprint $table) {
            $table->string('transaction_id', 255)->nullable()->change();
            $table->string('gateway_reference', 255)->nullable();
            $table->string('order_reference', 255)->nullable();
            $table->string('otp', 255)->nullable();
            $table->tinyInteger('otp_verified')->default(0)->nullable()->comments('0 = Not Verified, 1 = Verified');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn('gateway_reference');
            $table->dropColumn('order_reference');
            $table->dropColumn('otp');
            $table->dropColumn('otp_verified');
        });
    }
}
