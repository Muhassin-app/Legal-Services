<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfluAttrOptTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influ_attr_opt', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150)->nullable();
            $table->bigInteger('attribute_id')->unsigned()->nullable();
            $table->string('hexacode', 10)->nullable();
            $table->smallInteger('position')->default(1);
            $table->timestamps();

            $table->index('position');
            $table->foreign('attribute_id')->references('id')->on('influ_attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('influ_attr_opt');
    }
}
