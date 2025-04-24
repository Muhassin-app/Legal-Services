<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableVendorsAddRazorpay extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->longText('razorpay_contact_json')->nullable();
            $table->longText('razorpay_bank_json')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendors',function (Blueprint $table){
            $table->dropColumn('razorpay_contact_json');
            $table->dropColumn('razorpay_bank_json');
        });
    }
}
