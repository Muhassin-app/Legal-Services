<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableAddVendorIdToQrcodeImports extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('qrcode_imports', function (Blueprint $table) {
            if (!Schema::hasColumn('qrcode_imports', 'vendor_id')){
                $table->integer('vendor_id')->nullable();
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
        Schema::table('qrcode_imports', function (Blueprint $table) {
            //
        });
    }
}
