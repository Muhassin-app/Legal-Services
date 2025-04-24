<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMultipleColumnToCartProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->bigInteger('slot_id')->unsigned()->nullable();
            $table->date('delivery_date')->nullable();
            $table->decimal('slot_price', 12, 2)->nullable();

            $table->foreign('slot_id')->references('id')->on('delivery_slots')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dropColumn(['slot_id',  'delivery_date', 'slot_price']);
        });
    }
}
