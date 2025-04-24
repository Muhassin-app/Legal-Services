<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeToServiceareaforbanners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('service_area_for_banners', function (Blueprint $table) {
            $table->tinyInteger('type')->default(1)->comment('1-Web, 2-Mobile');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_area_for_banners', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
}
