<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAgentToTempCartProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('temp_cart_products', function (Blueprint $table) {
            $table->unsignedBigInteger('dispatch_agent_id')->nullable()->comment('driver id');
            $table->decimal('dispatch_agent_price',16,4)->default(0)->nullable();
            

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('temp_cart_products', function (Blueprint $table) {
            $table->dropColumn('dispatch_agent_id');
            $table->dropColumn('dispatch_agent_price');
        });
    }
}
