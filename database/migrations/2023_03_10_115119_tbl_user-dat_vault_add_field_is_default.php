<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TblUserDatVaultAddFieldIsDefault extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_data_vault', function (Blueprint $table) {
            $table->tinyInteger('is_default')->after('card_hint')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_data_vault', function (Blueprint $table) {
            $table->dropColumn('is_default');
        });
    }
}
