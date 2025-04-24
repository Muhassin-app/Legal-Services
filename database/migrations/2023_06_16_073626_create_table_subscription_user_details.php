<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableSubscriptionUserDetails extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('subscription_plan_user_details', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscription_invoice_id')->unsigned();
            $table->string('delivery_method', '16')->nullable();
            $table->string('meal_timing', 16)->nullable();
            $table->string('meal_package', 16)->nullable();
            $table->dateTime('start_date')
                ->nullable()
                ->comment('Date from which the plan will start');
            $table->text('delivery_instruction')->nullable();
            $table->json('days')
                ->nullable()
                ->comment('On which days of week the service will be provided');
            $table->bigInteger('address_id')->unsigned();
            $table->enum('autorenew', [
                0,
                1
            ])->comment('0=not autorenew, 1=autorenew');
            $table->integer('credit_left')->nullable();
            $table->timestamps();

            $table->foreign('subscription_invoice_id', 'fk_subscription_plan_user_details_subscription_invoice_id')
                ->references('id')
                ->on('subscription_invoices_user')
                ->onDelete('cascade');
            $table->foreign('address_id', 'fk_subscription_plan_user_details_address_id')
                ->references('id')
                ->on('user_addresses')
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
        Schema::table('subscription_plan_user_details', function (Blueprint $table) {
            $table->dropForeign([
                'fk_subscription_plan_user_details_subscription_invoice_id',
                'fk_subscription_plan_user_details_address_id'
            ]);
            $table->drop();
        });
    }
}
