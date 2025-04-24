<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaxesOnChargesToProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->tinyInteger('service_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->tinyInteger('delivery_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->tinyInteger('container_charges_tax')->default(0)->comment('1=active, 0=not');
            $table->unsignedBigInteger('service_charges_tax_id')->default(0);
            $table->unsignedBigInteger('delivery_charges_tax_id')->default(0);
            $table->unsignedBigInteger('container_charges_tax_id')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('service_charges_tax');
            $table->dropColumn('delivery_charges_tax');
            $table->dropColumn('container_charges_tax');
            $table->dropColumn('service_charges_tax_id');
            $table->dropColumn('delivery_charges_tax_id');
            $table->dropColumn('container_charges_tax_id');
        });
    }
}
