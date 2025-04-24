<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserRegistrationDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_registration_documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_type')->nullable();
            $table->tinyinteger('is_required')->nullable();
            $table->timestamps();
        });
        Schema::create('user_registration_document_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->mediumText('slug')->nullable();
            $table->bigInteger('language_id')->unsigned();
            $table->bigInteger('user_registration_document_id')->unsigned();
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
        Schema::dropIfExists('user_registration_documents');
    }
}
