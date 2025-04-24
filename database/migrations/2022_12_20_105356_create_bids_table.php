<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBidsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bids', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('bid_req_id')->nullable();
            $table->bigInteger('vendor_id')->nullable();
            $table->string('bid_total')->nullable();
            $table->string('discount')->nullable();
            $table->string('final_amount')->nullable();
            $table->tinyInteger('status')->default(0)->comment('0 Pending 1 Accepted 2 Rejected');
            $table->timestamps();

            $table->foreign('bid_req_id')->references('id')->on('bid_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bids');
    }
}
