<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPercentValueToSubscriptionInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_invoice_features_user', function (Blueprint $table) {
            $table->unsignedDecimal('percent_value', 5, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_invoice_features_user', function (Blueprint $table) {
            $table->dropColumn('percent_value');
        });
    }
}
