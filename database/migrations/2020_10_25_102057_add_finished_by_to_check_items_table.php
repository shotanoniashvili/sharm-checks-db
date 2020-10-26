<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFinishedByToCheckItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('check_items', function (Blueprint $table) {
            $table->unsignedBigInteger('finished_by')->nullable();

            $table->foreign('finished_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('check_items', function (Blueprint $table) {
            $table->dropForeign(['finished_by']);
            $table->dropColumn('finished_by');
        });
    }
}
