<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->date('next_due_date')->nullable()->after('visit_date');
            $table->unsignedInteger('recurrence_days')->nullable()->after('next_due_date');
            $table->timestamp('reminder_sent_at')->nullable()->after('recurrence_days');
        });
    }

    public function down(): void
    {
        Schema::table('health_records', function (Blueprint $table) {
            $table->dropColumn(['next_due_date', 'recurrence_days', 'reminder_sent_at']);
        });
    }
};
