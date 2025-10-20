<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('request_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('applier_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('attendance_id')->constrained()->onDelete('cascade');
            $table->foreignId('approver_id')->nullable()->constrained('admin_users')->onDelete('set null');
            $table->time('requested_work_start')->nullable();
            $table->time('requested_work_end')->nullable();
            $table->string('note');
            $table->boolean('is_approved')->default(false);
            $table->timestamp('created_at')->useCurrent()->nullable(false);
            $table->timestamp('updated_at')->useCurrent()->nullable(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('request_attendances');
    }
};
