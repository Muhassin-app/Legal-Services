<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTempCartsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_carts', function (Blueprint $table) {
            $table->id();
            $table->string('unique_identifier')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('status', ['0', '1', '2'])->comment('0-Active, 1-Blocked, 2-Deleted');
            $table->enum('is_gift', ['0','1'])->comment('0-Yes, 1-No');
            $table->integer('item_count')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->string('schedule_type')->nullable();
            $table->dateTimeTz('scheduled_date_time')->nullable();
            $table->text('specific_instructions')->nullable();
            $table->mediumText('comment_for_pickup_driver')->nullable();
            $table->mediumText('comment_for_dropoff_driver')->nullable();
            $table->mediumText('comment_for_vendor')->nullable();
            $table->dateTime('schedule_pickup')->nullable();
            $table->dateTime('schedule_dropoff')->nullable();
            $table->string('scheduled_slot')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('currency_id')->references('id')->on('currencies')->onDelete('set null');
        });

        Schema::create('temp_cart_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->unsignedBigInteger('vendor_dinein_table_id')->nullable();
            $table->integer('quantity')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->tinyInteger('status')->comment('0-Active, 1-Blocked, 2-Deleted');
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->tinyInteger('is_tax_applied')->comment('0-Yes, 1-No');
            $table->unsignedBigInteger('tax_rate_id')->nullable();
            $table->string('schedule_type',100)->nullable();
            $table->dateTime('scheduled_date_time')->nullable();
            $table->unsignedBigInteger('tax_category_id')->nullable();
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->bigInteger('luxury_option_id')->default('1');
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('temp_carts')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('variant_id')->references('id')->on('product_variants')->onDelete('set null');
            $table->foreign('tax_rate_id')->references('id')->on('tax_rates')->onDelete('set null');
            $table->foreign('tax_category_id')->references('id')->on('tax_categories')->onDelete('set null');
            $table->index('status');
            $table->index('is_tax_applied');
        });

        Schema::create('temp_cart_addons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('cart_product_id');
            $table->unsignedBigInteger('addon_id');
            $table->unsignedBigInteger('option_id')->nullable();
            $table->timestamps();

            $table->foreign('cart_product_id')->references('id')->on('temp_cart_products')->onDelete('cascade');
            $table->foreign('addon_id')->references('id')->on('addon_sets')->onDelete('cascade');
            $table->foreign('option_id')->references('id')->on('addon_options')->onDelete('cascade');
        });

        Schema::create('temp_cart_coupons', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('temp_carts')->onDelete('cascade');
            $table->foreign('coupon_id')->references('id')->on('promocodes')->onDelete('set null');
        });

        Schema::create('temp_cart_vendor_delivery_fee', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cart_id')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('delivery_fee', 64, 0)->default(0);
            $table->enum('shipping_delivery_type', ['D', 'L', 'S'])->default('D')->comment('D : Dispatcher , L : Lalamove ,S : Static');
            $table->timestamps();

            $table->foreign('cart_id')->references('id')->on('temp_carts')->onDelete('cascade');
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
        Schema::dropIfExists('temp_carts');
        Schema::dropIfExists('temp_cart_products');
        Schema::dropIfExists('temp_cart_addons');
        Schema::dropIfExists('temp_cart_coupons');
        Schema::dropIfExists('temp_cart_vendor_delivery_fee');
    }
}
