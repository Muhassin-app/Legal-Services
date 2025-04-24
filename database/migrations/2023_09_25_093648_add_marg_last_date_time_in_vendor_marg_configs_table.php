<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMargLastDateTimeInVendorMargConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_marg_configs', function (Blueprint $table) {
            $table->string('marg_last_date_time')->after('marg_date_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_marg_configs', function (Blueprint $table) {
            $table->dropColumn('marg_last_date_time');
        });
    }
}
