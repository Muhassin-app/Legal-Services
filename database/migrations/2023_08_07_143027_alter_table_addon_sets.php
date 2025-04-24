<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAddonSets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('addon_sets', function (Blueprint $table) {
            if(!Schema::hasColumn('addon_sets', 'icon')){
                $table->string('icon')->nullable();
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
        Schema::table('addon_sets', function (Blueprint $table) {
            if(Schema::hasColumn('addon_sets', 'icon')){
                $table->dropColumn('icon');
            }
        });
    }
}
