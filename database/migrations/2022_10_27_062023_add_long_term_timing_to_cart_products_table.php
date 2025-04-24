<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLongTermTimingToCartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cart_products', function (Blueprint $table) {
            $table->dateTime('service_start_date')->nullable();
            $table->string('service_day',150)->nullable()->comment('only day number (this is only for product is_long_term_service=1)');
            $table->string('service_date',150)->nullable()->comment('only date number (this is only for product is_long_term_service=1)');
            $table->string('service_period',150)->nullable()->comment('day,week,month (this is only for product is_long_term_service=1)');
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
            $table->dropColumn('service_start_date');
            $table->dropColumn('service_day');
            $table->dropColumn('service_date');
            $table->dropColumn('service_period');
        });
    }
}
