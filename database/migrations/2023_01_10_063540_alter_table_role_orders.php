<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableRoleOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('main_roles', function (Blueprint $table) {
            if (!Schema::hasColumn('main_roles', 'hierarchy_no'))
            {
                $table->integer('hierarchy_no')->default(0);
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
        Schema::table('main_roles', function (Blueprint $table) {
            $table->dropColumn('hierarchy_no');
        });

    }
}
