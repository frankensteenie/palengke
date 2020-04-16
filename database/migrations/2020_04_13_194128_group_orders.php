<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class GroupOrders extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('group_roders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('orders_id');
            $table->foreign('orders_id')->references('id')->on('orders');
            $table->tinyInteger('is_paid')->default(0);
            // rider
            $table->foreignId('riders_id')->nullable();
            $table->foreign('riders_id')->references('id')->on('users');
            $table->string('controlNumber');
            $table->tinyInteger('isForDelivery')->default(0);
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
        Schema::dropIfExists('group_roders');
    }
}
