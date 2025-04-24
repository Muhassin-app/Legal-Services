<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRescheduleOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('reschedule_orders')) {
            Schema::create('reschedule_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('reschedule_by')->references('id')->on('users')->onDelete('cascade');
                $table->foreignId('order_id')->references('id')->on('orders')->onDelete('cascade');
                $table->foreignId('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
                $table->string('prev_schedule_pickup')->nullable();
                $table->string('prev_schedule_dropoff')->nullable();
                $table->string('prev_scheduled_slot')->nullable();
                $table->string('prev_dropoff_scheduled_slot')->nullable();
                $table->string('new_schedule_pickup')->nullable();
                $table->string('new_schedule_dropoff')->nullable();
                $table->string('new_scheduled_slot')->nullable();
                $table->string('new_dropoff_scheduled_slot')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('reschedule_orders');
    }
}
