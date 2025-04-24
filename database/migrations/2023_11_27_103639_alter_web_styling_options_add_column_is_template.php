<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterWebStylingOptionsAddColumnIsTemplate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('web_styling_options', function (Blueprint $table) {
            $table->tinyInteger('is_template')->default(0);
        });
        Schema::table('app_styling_options', function (Blueprint $table) {
            $table->tinyInteger('is_template')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('web_styling_options', function (Blueprint $table) {
            $table->dropColumn('is_template');
        });
        Schema::table('app_styling_options', function (Blueprint $table) {
            $table->dropColumn('is_template');
        });
    }
}
