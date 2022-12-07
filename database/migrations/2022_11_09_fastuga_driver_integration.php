<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('drivers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('phone', 20);
            $table->string('license_plate', 9);

            $table->json('custom')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('orders_driver_delivery', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('order_id')->unsigned();
            $table->foreign('order_id')->references('id')->on('orders');

            $table->string('delivery_location');

            $table->integer('tax_fee');

            $table->timestamp('delivery_started_at')->nullable();
            $table->timestamp('delivery_ended_at')->nullable();

            $table->json('custom')->nullable();
            $table->float('distance', 8, 2);
            $table->unique(['order_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('drivers');
        Schema::dropIfExists('orders_driver_delivery');
    }
};
