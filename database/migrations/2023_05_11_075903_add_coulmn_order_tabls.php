<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoulmnOrderTabls extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('old_payable_amount',10,2)->default(0);
            $table->decimal('total_waiting_time',10,2)->default(0);
            $table->decimal('total_waiting_price',10,2)->default(0);
        });

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->decimal('waiting_time',10,2)->default(0);
            $table->decimal('waiting_price',10,2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('total_waiting_time');
            $table->dropColumn('total_waiting_price');
            $table->dropColumn('old_payable_amount');
        });

        Schema::table('order_vendors', function (Blueprint $table) {
            $table->dropColumn('waiting_time');
            $table->dropColumn('waiting_price');
        });
    }
    
}
