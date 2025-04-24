<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGiftCardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('title')->nullable();
            $table->string('image')->nullable();
            $table->string('short_desc')->nullable();
            $table->decimal('amount',12,2)->unsigned()->nullable();
            $table->timestamp('expiry_date')->nullable();
            $table->tinyInteger('is_deleted')->default(0)->comment('0- No, 1- yes')->nullable();
            $table->bigInteger('added_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('added_by')->references('id')->on('users')->onDelete('set null');
    
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('gift_cards');
    }
}
