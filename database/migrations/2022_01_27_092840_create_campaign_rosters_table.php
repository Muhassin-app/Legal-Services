<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignRostersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaign_rosters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('campaign_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->dateTime('notification_time')->nullable();
            $table->integer('notofication_type')->comment('1 sms, 2 email, 3 push notification')->nullable();
            $table->string('device_type',50)->nullable();
            $table->string('device_token',191)->nullable();
            $table->integer('status')->nullable();
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
        Schema::dropIfExists('campaign_rosters');
    }
}
