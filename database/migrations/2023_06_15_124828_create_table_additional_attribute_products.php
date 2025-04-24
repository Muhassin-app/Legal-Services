<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableAdditionalAttributeProducts extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('additional_attribute_products', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->bigInteger('additional_attribute_id')->unsigned();
            $table->bigInteger('reference_id')->nullable()->comment('Id of any another table where attributes are bring used.');
            $table->mediumText('product_data')->nullable();
            $table->timestamps();

            $table->foreign('additional_attribute_id', 'fk_additional_attribute_products_additional_attribute_id')->references('id')->on('additional_attributes')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('additional_attribute_products', function (Blueprint $table) {
            $table->dropForeign('fk_additional_attribute_products_additional_attribute_id');
            $table->drop();
        });
    }
}
