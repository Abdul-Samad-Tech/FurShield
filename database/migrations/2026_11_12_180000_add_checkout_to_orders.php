<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('contact_name')->nullable();
            $table->string('contact_phone', 30)->nullable();
            $table->string('contact_email')->nullable();
            $table->string('fulfillment', 20)->nullable();
            $table->text('address_line')->nullable();
            $table->string('city')->nullable();
            $table->text('notes')->nullable();
            $table->string('payment_method', 30)->nullable();
            $table->string('payment_status', 20)->nullable();
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('method', 30);
            $table->string('status', 20);
            $table->decimal('amount', 10, 2);
            $table->string('reference', 32)->unique();
            $table->string('card_brand', 20)->nullable();
            $table->string('card_last4', 4)->nullable();
            $table->string('failure_reason')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'contact_name',
                'contact_phone',
                'contact_email',
                'fulfillment',
                'address_line',
                'city',
                'notes',
                'payment_method',
                'payment_status',
            ]);
        });
    }
};
