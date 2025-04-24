<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdditionalAttribute extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('title', 64)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->tinyInteger('is_required')->nullable();
            $table->string('field_type', 16)->nullable();
            $table->string('service_type', 32)->nullable();
            $table->integer('type_id')->nullable();
            $table->smallInteger('position')->nullable();
            $table->tinyInteger('status')
                ->default(0)
                ->comment('1 for yes, 0 for no');
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
        Schema::dropIfExists('additional_attributes');
    }
}
