<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShowQrOnFooterFieldToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('show_qr_on_footer')->comment('0-No, 1-Yes')->default(0);
            $table->tinyInteger('auto_implement_5_percent_tip')->comment('0-No, 1-Yes')->default(0);
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
            $table->dropColumn('show_qr_on_footer');
            $table->dropColumn('auto_implement_5_percent_tip');
        });
    }
}
