<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_privileges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('privilege_key');
            $table->boolean('is_allowed')->default(false);
            $table->timestamps();
            $table->unique(['user_id', 'privilege_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_privileges');
    }
};
