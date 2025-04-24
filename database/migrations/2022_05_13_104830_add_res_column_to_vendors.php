<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddResColumnToVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'rescheduling_charges')) {
                $table->decimal('rescheduling_charges', 64, 0)->default(0)->comment('enable for laundry');
                $table->decimal('pickup_cancelling_charges', 64, 0)->nullable()->default(0)->after('rescheduling_charges');
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
        Schema::table('vendors', function (Blueprint $table) {
            $table->decimal('rescheduling_charges');
            $table->dropColumn('pickup_cancelling_charges');
        });
    }
}
