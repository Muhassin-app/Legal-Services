<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnToProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            if(!Schema::hasColumn('products','captain_name')){
                $table->string('captain_name', 32)->nullable();
            }
            if(!Schema::hasColumn('products','captain_profile')){
                $table->string('captain_profile', 255)->nullable();
            }
            if(!Schema::hasColumn('products','captain_description')){
                $table->string('captain_description', 255)->nullable();
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
        Schema::table('products', function (Blueprint $table) {
            if(Schema::hasColumn('products','captain_name')){
                $table->dropColumn('captain_name');
            }
            if(Schema::hasColumn('products','captain_profile')){
                $table->dropColumn('captain_profile');
            }
            if(Schema::hasColumn('products','captain_description')){
                $table->dropColumn('captain_description');
            }
        });
    }
}
