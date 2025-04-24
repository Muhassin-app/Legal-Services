<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeDatatypeInSubscriptionInvoicesVendorTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_invoices_vendor', function (Blueprint $table) {
            $table->decimal('subscription_amount', 16, 8)->nullable()->change();
            $table->decimal('discount_amount', 16, 8)->nullable()->change();
            $table->decimal('paid_amount', 16, 8)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_invoices_vendor', function (Blueprint $table) {
            //
        });
    }
}
