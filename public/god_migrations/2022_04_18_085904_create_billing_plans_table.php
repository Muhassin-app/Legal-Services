<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBillingPlansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_plans', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('slug', 150);
            $table->string('title', 150);
            $table->string('image', 200);
            $table->timestamps();
            $table->tinyInteger('status')->comment('(0=>Pending,1=>active, 2=>in active)');
            $table->tinyInteger('plan_type')->comment('(1=>Software Licence,2=>hosting)');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_plans');
    }
}
