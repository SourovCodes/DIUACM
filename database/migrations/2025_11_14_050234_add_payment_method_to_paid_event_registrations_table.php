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
        Schema::table('paid_event_registrations', function (Blueprint $table) {
            $table->string('payment_method')->default('sslcommerz')->after('amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paid_event_registrations', function (Blueprint $table) {
            $table->dropColumn('payment_method');
        });
    }
};
