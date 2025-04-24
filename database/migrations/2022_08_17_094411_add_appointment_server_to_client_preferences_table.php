<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAppointmentServerToClientPreferencesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->string('need_appointment_service')->nullable();
            $table->string('appointment_service_key')->nullable();
            $table->string('appointment_service_key_url')->nullable();
            $table->string('appointment_service_key_code')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_preferences', function (Blueprint $table) {
            $table->dropColumn('need_appointment_service');
            $table->dropColumn('appointment_service_key');
            $table->dropColumn('appointment_service_key_url');
            $table->dropColumn('appointment_service_key_code');
        });
    }
}
