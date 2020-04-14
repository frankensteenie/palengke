<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Orders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // client
            $table->foreignId('users_id');
            $table->foreign('users_id')->references('id')->on('users');
            // seller
            $table->foreignId('sellers_id');
            $table->foreign('sellers_id')->references('id')->on('users');
            // items
            $table->foreignId('items_id');
            $table->foreign('items_id')->references('id')->on('sellers');

            $table->string('quantity')->default(1);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('orders');
    }
}
