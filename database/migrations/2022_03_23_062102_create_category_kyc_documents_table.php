<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCategoryKycDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('category_kyc_documents', function (Blueprint $table) {
            $table->id();
            $table->string('file_type')->nullable();
            $table->tinyinteger('is_required')->nullable();
            $table->timestamps();
        });
        Schema::create('category_kyc_document_translations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->mediumText('slug')->nullable();
            $table->bigInteger('language_id')->unsigned();
            $table->bigInteger('category_kyc_document_id')->unsigned();
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
        Schema::dropIfExists('category_kyc_documents');
        Schema::dropIfExists('category_kyc_document_translations');
    }
}
