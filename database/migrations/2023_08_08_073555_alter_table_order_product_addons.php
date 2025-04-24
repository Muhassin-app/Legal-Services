<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableOrderProductAddons extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_product_addons', function (Blueprint $table) {
            if(!Schema::hasColumn('order_product_addons', 'addon_count')){
                $table->string('addon_count')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_product_addons', function (Blueprint $table) {
            if(Schema::hasColumn('order_product_addons', 'addon_count')){
                $table->dropColumn('addon_count');
            }
        });
    }
}
