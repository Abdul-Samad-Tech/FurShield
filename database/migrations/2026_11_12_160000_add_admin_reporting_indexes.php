<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->index('role', 'users_role_index');
            $table->index('name', 'users_name_index');
            $table->index('created_at', 'users_created_at_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->index('status', 'appointments_status_index');
            $table->index('created_at', 'appointments_created_at_index');
            $table->index('appointment_time', 'appointments_appointment_time_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index(['status', 'order_date'], 'orders_status_order_date_index');
            $table->index('created_at', 'orders_created_at_index');
        });

        Schema::table('adoption_listings', function (Blueprint $table) {
            $table->index('status', 'adoption_listings_status_index');
            $table->index('created_at', 'adoption_listings_created_at_index');
        });

        Schema::table('adoption_interests', function (Blueprint $table) {
            $table->index('status', 'adoption_interests_status_index');
            $table->index('created_at', 'adoption_interests_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('users_role_index');
            $table->dropIndex('users_name_index');
            $table->dropIndex('users_created_at_index');
        });

        Schema::table('appointments', function (Blueprint $table) {
            $table->dropIndex('appointments_status_index');
            $table->dropIndex('appointments_created_at_index');
            $table->dropIndex('appointments_appointment_time_index');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex('orders_status_order_date_index');
            $table->dropIndex('orders_created_at_index');
        });

        Schema::table('adoption_listings', function (Blueprint $table) {
            $table->dropIndex('adoption_listings_status_index');
            $table->dropIndex('adoption_listings_created_at_index');
        });

        Schema::table('adoption_interests', function (Blueprint $table) {
            $table->dropIndex('adoption_interests_status_index');
            $table->dropIndex('adoption_interests_created_at_index');
        });
    }
};
