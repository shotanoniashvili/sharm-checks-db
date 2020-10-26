<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckItemStatusesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_item_statuses', function (Blueprint $table) {
            $table->unsignedBigInteger('check_item_id');
            $table->unsignedBigInteger('user_id');
            $table->boolean('is_accepted');
            $table->timestamps();

            $table->primary(['check_item_id', 'user_id']);
            $table->foreign('check_item_id')->references('id')->on('check_items')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('check_item_statuses');
    }
}
