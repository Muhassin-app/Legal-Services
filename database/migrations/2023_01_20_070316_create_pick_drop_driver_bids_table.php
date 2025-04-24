<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePickDropDriverBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pick_drop_driver_bids', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_bid_id')->nullable(); 
            $table->tinyInteger('status')->default(0)->comment('0=>placed, 1=>approved, 2=>declined');
            $table->string('tasks', 1000)->nullable();
            $table->integer('driver_id')->nullable();
            $table->string('driver_name')->nullable();
            $table->string('driver_image')->nullable();
            $table->decimal('bid_price', 15, 4)->nullable();
            $table->string('task_type')->nullable();
            $table->dateTime('expired_at', $precision = 0);
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
        Schema::dropIfExists('pick_drop_driver_bids');
    }
}
