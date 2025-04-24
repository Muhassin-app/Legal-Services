<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToSubscriptionPlansUser extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('subscription_plans_user', function (Blueprint $table) {
            $table->integer('type_id')->nullable()->after('sort_order');
            $table->integer('order_limit')->nullable()->after('type_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plans_user', function (Blueprint $table) {
            $table->dropColumn([
                'type_id',
                'order_limit'
            ]);
        });
    }
}
