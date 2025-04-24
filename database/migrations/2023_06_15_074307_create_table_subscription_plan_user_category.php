<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubscriptionPlanUserCategory extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plan_user_category', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscription_id')->unsigned();
            $table->bigInteger('category_id')->unsigned();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('subscription_id', 'fk_subscription_plan_user_category_subscription_id')
                ->references('id')
                ->on('subscription_plans_user')
                ->onDelete('cascade');
            $table->foreign('category_id', 'fk_subscription_plan_user_category_category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('subscription_plan_user_category', function (Blueprint $table) {
            $table->dropForeign([
                'fk_subscription_plan_user_category_subscription_id',
                'fk_subscription_plan_user_category_category_id'
            ]);
            $table->drop();
        });
    }
}
