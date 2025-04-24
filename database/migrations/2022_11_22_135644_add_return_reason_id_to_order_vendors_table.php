<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddReturnReasonIdToOrderVendorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            $table->bigInteger('return_reason_id')->unsigned()->nullable();

            $table->foreign('return_reason_id')->references('id')->on('return_reasons')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_vendors', function (Blueprint $table) {
            DB::statement('ALTER TABLE order_vendors DROP FOREIGN KEY order_vendors_return_reason_id_foreign');
            $table->dropColumn('return_reason_id');
        });
    }
}
