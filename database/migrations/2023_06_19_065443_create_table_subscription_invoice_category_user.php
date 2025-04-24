<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubscriptionInvoiceCategoryUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_invoice_category_user', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscription_invoice_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->string('category_title', 64)->nullable();
            $table->timestamps();
            
            $table->foreign('subscription_invoice_id', 'fk_subscription_invoice_category_user_subscription_invoice_id')->references('id')->on('subscription_invoices_user')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_invoice_category_user', function (Blueprint $table) {
            $table->dropForeign('fk_subscription_invoice_category_user_subscription_invoice_id');
            $table->drop();
        });
    }
}
