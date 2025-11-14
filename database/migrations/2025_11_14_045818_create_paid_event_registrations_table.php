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
        Schema::create('paid_event_registrations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paid_event_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Personal Information
            $table->string('name');
            $table->string('email');
            $table->string('student_id');
            $table->string('phone');
            $table->string('section');
            $table->string('department');
            $table->string('lab_teacher_name');

            // Event Details
            $table->string('tshirt_size');
            $table->string('gender');
            $table->boolean('transport_service_required')->default(false);
            $table->string('pickup_point')->nullable();

            // Payment Information
            $table->decimal('amount', 10, 2);
            $table->string('status')->default('pending');

            $table->timestamps();

            // Ensure one registration per user per event
            $table->unique(['paid_event_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('paid_event_registrations');
    }
};
