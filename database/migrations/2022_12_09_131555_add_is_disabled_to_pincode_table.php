<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsDisabledToPincodeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pincodes', function (Blueprint $table) {
            $table->tinyInteger('is_disabled')->default(0)->comment('0 for enabled, 1 for disabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pincodes', function (Blueprint $table) {
            $table->dropColumn('is_disabled');
        });
    }
}
