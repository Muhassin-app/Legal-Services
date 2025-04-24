<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDeliverySlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        
        Schema::create('delivery_slots', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('start_time');
            $table->string('end_time');
            $table->decimal('price', 12, 2);
            $table->tinyInteger('status')->default(0)->comment('0 for enabled, 1 for disabled');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('delivery_slots');
    }
}
