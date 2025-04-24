<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfluencerUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influencer_users', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->bigInteger('influencer_tier_id')->unsigned()->nullable();
            $table->tinyInteger('commision_type')->comment('1=Percentage, 2=fixed')->nullable();
            $table->integer('commision')->comment('commision percentage or amount')->nullable();
            $table->string('reffered_code')->nullable();
            $table->tinyInteger('is_approved')->default(0);
            $table->tinyInteger('status')->comment('1=active, 0=inactive')->default(0);
            $table->timestamps();
            
            $table->foreign('influencer_tier_id')->references('id')->on('influencer_tiers')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('influencer_users');
    }
}
