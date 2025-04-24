<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNullableColumnToAuthenticationLog extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (Schema::hasTable('authentication_log')) {
            Schema::table('authentication_log', function (Blueprint $table) {
                $table->bigInteger('authenticatable_id')->nullable()->unsigned()->change(); 
            });
         }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('authentication_log', function (Blueprint $table) {
            //
        });
    }
}
