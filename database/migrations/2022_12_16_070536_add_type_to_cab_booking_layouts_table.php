<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToCabBookingLayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cab_booking_layouts', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->comment('1 = web, 2 = app')->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cab_booking_layouts', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
