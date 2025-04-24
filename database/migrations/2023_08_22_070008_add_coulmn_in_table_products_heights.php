<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCoulmnInTableProductsHeights extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
                if(!Schema::hasColumn('products', 'height')){
                    $table->decimal('height', 10, 4)->nullable();
                }
                if(!Schema::hasColumn('products', 'breadth')){
                    $table->decimal('breadth', 10, 4)->nullable();
                }
                if(!Schema::hasColumn('products', 'length')){
                    $table->decimal('length', 10, 4)->nullable();
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
            //
        });
    }
}
