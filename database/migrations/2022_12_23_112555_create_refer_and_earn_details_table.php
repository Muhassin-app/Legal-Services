<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReferAndEarnDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('refer_and_earn_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('attribute_id')->unsigned()->nullable(); 
            $table->bigInteger('attribute_option_id')->unsigned()->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('influencer_user_id')->unsigned()->nullable();
            $table->string('key_name')->nullable();
            $table->string('key_value')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('attribute_id')->references('id')->on('influ_attributes')->onDelete('cascade');
            $table->foreign('attribute_option_id')->references('id')->on('influ_attr_opt')->onDelete('cascade');
            $table->foreign('influencer_user_id')->references('id')->on('influencer_users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('refer_and_earn_details');
    }
}
