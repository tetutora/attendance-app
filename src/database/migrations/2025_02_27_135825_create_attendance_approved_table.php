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
        Schema::create('attendance_approved', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('attendance_id')->nullable();
            $table->foreign('attendance_id')->references('id')->on('attendances')->onDelete('cascade');
            $table->unsignedBigInteger('approval_status_id')->default(2);
            $table->date('attendance_date');
            $table->time('clock_in');
            $table->time('clock_out');
            $table->time('break_in');
            $table->time('break_out');
            $table->integer('break_time');
            $table->integer('work_time');
            $table->text('remarks');
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendance_approved');
    }
};
