<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableLumenClients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lumen_clients', function (Blueprint $table) {
            $table->id();
            $table->string('database_name',50);
            $table->string('domain',50)->nullable();
            $table->string('code',10);
            $table->string('lumen_access_token',60)->nullable();
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
        Schema::dropIfExists('lumen_clients');
    }
}
