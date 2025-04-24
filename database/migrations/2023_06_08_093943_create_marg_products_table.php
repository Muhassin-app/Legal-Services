<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMargProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marg_products', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('product_id')->unsigned()->nullable();
            $table->integer('rid')->unsigned()->nullable();// $request->rid;    //: "34347992"
            $table->string('catcode', 10)->nullable();// $request->catcode;    //: "      "
            $table->string('code', 30)->nullable();// $request->code;    //: "1020044"
            $table->string('name', 50)->nullable();// $request->name;    //: "testex"
            $table->integer('stock')->default(0);// $request->stock;    //: "0.000"
            $table->string('remark')->nullable();// $request->remark;    //: ""
            $table->text('company')->nullable();;// $request->company;    //: "E.MERK PVT.LTD."
            $table->string('shopcode')->nullable();// $request->shopcode;    //: ""
            $table->double('MRP')->nullable();// $request->MRP;    //: "0.00"
            $table->double('Rate')->nullable();// $request->Rate;    //: "0.00"
            $table->double('Deal')->nullable();// $request->Deal;    //: "0"
            $table->double('Free')->nullable();// $request->Free;    //: "0"
            $table->double('PRate')->nullable();// $request->PRate;    //: "0.00"
            $table->tinyInteger('Is_Deleted')->default(0)->comment('0 - no, 1 - yes');// $request->Is_Deleted;    //: "0"
            $table->string('curbatch')->nullable();// $request->curbatch;    //: ""
            $table->string('exp')->nullable();// $request->exp;    //: "        "
            $table->string('gcode')->nullable();// $request->gcode;    //: "$40   "
            $table->string('MargCode')->nullable();// $request->MargCode;    //: ""
            $table->string('Conversion')->nullable();// $request->Conversion;    //: "0"
            $table->string('Salt')->nullable();// $request->Salt;    //: "A"
            $table->string('ENCODE')->nullable();// $request->ENCODE;    //: ""
            $table->string('remarks')->nullable();// $request->remarks;    //: "T;6;2; ;0;;;;;;Pcs ;;;0;6;;9037;"
            $table->string('Gcode6')->nullable();// $request->Gcode6;    //: "KC"
            $table->string('ProductCode')->nullable();// $request->ProductCode;    //: "9037"
            $table->timestamps();

            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->index('ProductCode');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marg_products');
    }
}
