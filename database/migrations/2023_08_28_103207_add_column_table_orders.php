<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnTableOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('orders', function (Blueprint $table) {
            if(!Schema::hasColumn('orders','flight_no')){
                $table->string('flight_no', 16)->nullable();
            }
            if(!Schema::hasColumn('orders','adults')){
                $table->integer('adults')->nullable();
            }
            if(!Schema::hasColumn('orders','name_sign_board')){
                $table->string('name_sign_board')->nullable();
            }
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
            if(Schema::hasColumn('orders','flight_no')){
                $table->dropColumn('flight_no');
            }
            if(Schema::hasColumn('orders','adults')){
                $table->dropColumn('adults');
            }
            if(Schema::hasColumn('orders','name_sign_board')){
                $table->dropColumn('name_sign_board');
            }
        });
    }
}
