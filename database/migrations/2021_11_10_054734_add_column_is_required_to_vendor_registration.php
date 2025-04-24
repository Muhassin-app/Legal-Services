<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnIsRequiredToVendorRegistration extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('vendor_registration_documents', function (Blueprint $table) {
            $table->integer('is_required')->default(1)->after('file_type')->comment('0 means not required, 1 means required');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('vendor_registration_documents', function (Blueprint $table) {
            $table->dropColumn('is_required');
        });
    }
}
