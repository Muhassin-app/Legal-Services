<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTypeCoulmnInHomeProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('home_products', function (Blueprint $table) {
            $table->tinyInteger('type')->default(0)->comment('0 - Web, 1 - App');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('home_products', function (Blueprint $table) {
            $table->removeColumn('type');
        });
    }
}
