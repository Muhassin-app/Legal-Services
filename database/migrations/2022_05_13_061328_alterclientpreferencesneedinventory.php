<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Alterclientpreferencesneedinventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->tinyInteger('need_inventory_service')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->string('inventory_service_key')->nullable();
            $table->string('inventory_service_key_url')->nullable();
            $table->string('inventory_service_key_code')->nullable();

            $table->tinyInteger('enable_inventory_service')->nullable()->default(0)->comment('0-No, 1-Yes');
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
            $table->dropColumn('need_inventory_service');
            $table->dropColumn('dispacher_home_other_service_key');
            $table->dropColumn('inventory_service_key');
            $table->dropColumn('inventory_service_key_code');

            $table->dropColumn('enable_inventory_service');
        });
    }
}
