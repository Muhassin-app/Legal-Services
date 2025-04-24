<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableRentalProtection extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!checkTableExists('rental_protections')) {
            Schema::create('rental_protections', function (Blueprint $table) {
                $table->id();
                $table->string('title', 32);
                $table->text('description');
                $table->decimal('price');
                $table->integer('validity')->comment('1 = day, 2 = week, 3 = month')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('rental_protections');
    }
}
