<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInfluencerTiersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('influencer_tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->integer('target')->nullable();
            $table->tinyInteger('commision_type')->comment('1=Percentage, 2=fixed')->nullable();
            $table->integer('commision')->comment('commision percentage or amount')->nullable();
            $table->tinyInteger('status')->comment('1=active, 0=Inactive')->nullable();
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
        Schema::dropIfExists('influencer_tiers');
    }
}
