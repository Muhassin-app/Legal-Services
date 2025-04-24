<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCartVendorDeliveryFee extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cart_vendor_delivery_fee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('delivery_fee', 64, 0)->default(0);
            $table->enum('shipping_delivery_type', ['D', 'L', 'S'])->default('D')->comment('D : Dispatcher , L : Lalamove ,S : Static');
            $table->timestamps();



            $table->foreign('cart_id')->references('id')->on('carts')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cart_vendor_delivery_fee');
    }
}
