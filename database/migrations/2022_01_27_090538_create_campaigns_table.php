<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->integer('type')->comment('1 SMS, 2 Email, 3 Push Notification')->default(1);
            $table->string('sms_text',160)->nullable();
            $table->string('email_title',191)->nullable();
            $table->string('email_subject',191)->nullable();
            $table->longText('email_body')->nullable();
            $table->string('push_title',191)->nullable();
            $table->string('push_image',250)->nullable();
            $table->longText('push_message_body')->nullable();
            $table->integer('push_url_option')->nullable()->comment('1 URL, 2 Category, 3 Vendor');
            $table->string('push_url_option_value',191)->nullable();
            $table->integer('send_to')->default(1)->comment('1 All, 2 Vendors');
            $table->dateTime('schedule_datetime')->nullable();
            $table->bigInteger('request_user_count')->default(1);
            $table->string('request_time_difference')->nullable();
            $table->bigInteger('total_request_count')->nullable();
            $table->integer('status')->comment('1 Active, 2 Pause, 3 Finish');
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
        Schema::dropIfExists('campaigns');
    }
}
