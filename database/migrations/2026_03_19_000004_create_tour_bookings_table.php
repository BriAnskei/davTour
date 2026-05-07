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
        Schema::create('tour_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');       // reference to users table
            $table->unsignedBigInteger('tour_sched_id'); // reference to tour_schedules table
            $table->integer('p_count');                  // number of people
            $table->string('status');                    // booking status (e.g., pending, confirmed)
            $table->timestamps();


            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tour_sched_id')->references('id')->on('tour_schedules')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tour_bookings');
    }
};