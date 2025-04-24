<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVendorMargConfigsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('vendor_marg_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id');
            $table->string('is_marg_enable');
            $table->string('marg_company_url');
            $table->string('marg_company_code');
            $table->string('marg_access_token');
            $table->string('marg_decrypt_key');
            $table->string('marg_date_time')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('vendor_marg_configs');
    }
}
