<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrderNotificationsLogs extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('order_notifications_logs', function (Blueprint $table) {
            $table->id();
            $table->Integer('order_id')->nullable();
            $table->Integer('order_vendor_id')->nullable();
            $table->Integer('order_number')->nullable();
            $table->Integer('user_id')->nullable();
            $table->Integer('vendor_id')->nullable();
            $table->string('message')->nullable();
            $table->tinyInteger('is_seen')->default(0);
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
        Schema::dropIfExists('order_notifications_logs');
    }
}
