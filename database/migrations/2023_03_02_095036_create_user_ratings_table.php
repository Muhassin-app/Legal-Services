<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRatingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->bigInteger('order_id')->unsigned()->nullable()->index();
            $table->bigInteger('order_vendor_id')->unsigned()->nullable()->index();
            $table->bigInteger('order_vendor_product_id')->unsigned()->nullable()->index();
            $table->string('order_type')->nullable()->comment('order_type 1=dispatch order on vendor base , 2=dispatch order on vendor base base.');
            $table->decimal('rating', 4, 2)->nullable();
            $table->string('review', 500)->nullable()->comment('user average rating.');;
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
        Schema::dropIfExists('user_ratings');
    }
}
