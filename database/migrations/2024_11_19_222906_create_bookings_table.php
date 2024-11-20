<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->integer('creator_id');
            $table->integer('service_id');
            $table->integer('package_id');
            $table->date('date');
            $table->time('time');
            $table->string('email')->default('');
            $table->string('lat')->default('');
            $table->string('longi')->default('');
            $table->string('location')->default('');
            $table->integer('status')->default(0)->comment('0-requested, 1-accepted/prepraing, 2-on the road, 3-completed');
            $table->string('payment_method')->default('');
            $table->longText('payment_id')->default('');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
