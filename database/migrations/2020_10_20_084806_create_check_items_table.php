<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('check_id');
            $table->string('op_name');
            $table->string('op_number');
            $table->decimal('gel')->nullable();
            $table->decimal('eur')->nullable();
            $table->decimal('rur')->nullable();
            $table->decimal('usd')->nullable();
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
            $table->string('brand')->nullable();
            $table->string('doc_type')->nullable();
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('check_id')->references('id')->on('checks')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_items');
    }
}
