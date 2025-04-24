<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingTimeframesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_timeframes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('slug', 150);
            $table->string('title', 150);
            $table->tinyInteger('is_custom')->comment('(1=>Yes,2=>No)');
            $table->tinyInteger('is_timelimit')->comment('(1=>Yes,2=>No)');
            $table->integer('standard_buffer_period')->comment('days');
            $table->timestamps();
            $table->tinyInteger('status')->comment('(0=>Pending,1=>active, 2=>in active)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_timeframes');
    }
}
