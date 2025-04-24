<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProducts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('engine', 16)->nullable();
            $table->string('boot_space', 16)->nullable();
            $table->string('mileage', 16)->nullable();
            $table->string('body_type', 16)->nullable();
            $table->integer('no_of_cylinder')->nullable();
            $table->string('max_torque', 16)->nullable();
            $table->string('fuel_tank_capacity', 8)->nullable();
            $table->string('ground_clearence', 8)->nullable();
            $table->string('bhp', 8)->nullable();
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
            $table->dropColumn(['engine', 'boot_space', 'mileage', 'body_type', 'no_of_cylinder', 'max_torque', 'fuel_tank_capacity', 'ground_clearence', 'bhp']);
        });
    }
}
