<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientSlotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('client_slots')) {
                Schema::create('client_slots', function (Blueprint $table) {
                    $table->id();
                    $table->string('name')->nullable();
                    $table->string('start_time')->nullable();
                    $table->string('end_time')->nullable();
                    $table->date('date')->nullable();
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
        Schema::dropIfExists('client_slots');
    }
}
