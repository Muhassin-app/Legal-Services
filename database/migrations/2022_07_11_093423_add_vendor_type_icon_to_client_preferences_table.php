<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddVendorTypeIconToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('rentalicon')->nullable()->after('takewayicon');
            $table->string('pick_dropicon')->nullable()->after('rentalicon');
            $table->string('on_demandicon')->nullable()->after('pick_dropicon');
            $table->string('laundryicon')->nullable()->after('on_demandicon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            //
        });
    }
}
