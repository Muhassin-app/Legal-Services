<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDispatcherToOrderLongTermServiceSchedulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_long_term_service_schedules', function (Blueprint $table) {
            $table->string('web_hook_code')->nullable()->comment('dispatcher weweb_hook_codev  dispatch');
            $table->string('dispatch_traking_url')->nullable()->comment('product dispatch');
            $table->string('dispatcher_status_option_id')->nullable()->comment('product dispatch');
            $table->string('order_status_option_id')->nullable()->comment('product dispatch');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_long_term_service_schedules', function (Blueprint $table) {
            $table->dropColumn('web_hook_code');
            $table->dropColumn('dispatch_traking_url');
            $table->dropColumn('dispatcher_status_option_id');
            $table->dropColumn('order_status_option_id');
        });
    }
}
