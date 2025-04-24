<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddViewestimationColumnToClientPreferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            if (!Schema::hasColumn('client_preferences', 'view_get_estimation_in_category')) {
             $table->tinyInteger('view_get_estimation_in_category')->nullable()->default(0)->after('get_estimations')->comment('0-off, 1-on');
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
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('view_get_estimation_in_category');
        });
    }
}
