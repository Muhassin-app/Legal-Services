<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AltervendorOrderDispatcherStatusestypeley extends Migration
{
   /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_order_dispatcher_statuses', function (Blueprint $table) {
            $table->enum('type', ['1', '2'])->default('1')->comment('1 : pickup , 2 : drop');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_order_dispatcher_statuses', function (Blueprint $table) {
            $table->dropColumn('type');
         });
    }
}
