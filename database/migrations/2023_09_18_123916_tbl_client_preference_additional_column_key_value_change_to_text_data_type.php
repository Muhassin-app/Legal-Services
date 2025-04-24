<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TblClientPreferenceAdditionalColumnKeyValueChangeToTextDataType extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preference_additional', function (Blueprint $table) {
            $table->text('key_value')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preference_additional', function (Blueprint $table) {
            $table->string('key_value')->change();
        });
    }
}
