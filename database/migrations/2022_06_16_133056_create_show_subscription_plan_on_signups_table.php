<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateShowSubscriptionPlanOnSignupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('show_subscription_plan_on_signups', function (Blueprint $table) {
            $table->id();
            $table->tinyInteger('show_plan_customer')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('every_sign_up')->nullable()->default(0)->comment('0-No, 1-Yes');
            $table->tinyInteger('every_app_open')->nullable()->default(0)->comment('0-No, 1-Yes');
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
        Schema::dropIfExists('show_subscription_plan_on_signups');
    }
}
