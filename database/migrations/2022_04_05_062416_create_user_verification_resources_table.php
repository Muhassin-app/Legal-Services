<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserVerificationResourcesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_verification_resources', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_verification_id')->unsigned();
            $table->string('type');
            $table->json('datapoints')->nullable()->comment('datapoints in json format');
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
        Schema::dropIfExists('user_verification_resources');
    }
}
