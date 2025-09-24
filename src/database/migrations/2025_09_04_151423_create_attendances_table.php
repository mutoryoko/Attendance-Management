<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date')->nullable();
            $table->time('clock_in_time')->nullable();
            $table->time('clock_out_time')->nullable();
            $table->integer('total_break_minutes')->default(0);
            $table->integer('total_work_minutes')->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'work_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
