<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDescriptionToBidRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bid_requests', function (Blueprint $table) {
            $table->string('bid_number');
            $table->string('description')->nullable();
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->string('bid_order_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bid_requests', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->dropColumn('bid_number');
        });

        Schema::table('bids', function (Blueprint $table) {
            $table->dropColumn('bid_order_number');
        });
    }
}
