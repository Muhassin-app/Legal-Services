<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderLongTermServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_long_term_service_schedules', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_long_term_services_id')->unsigned()->nullable();
            $table->dateTime('schedule_date')->nullable();
            $table->tinyInteger('status')->nullable()->default(0)->comment('0-not completed, 1-completed');
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
        Schema::dropIfExists('order_long_term_service_schedules');
    }
}
