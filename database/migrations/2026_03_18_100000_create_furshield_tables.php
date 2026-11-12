<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('owner')->after('email');
            $table->string('phone', 30)->nullable()->after('role');
            $table->text('address')->nullable()->after('phone');
            $table->string('shelter_name')->nullable()->after('address');
            $table->string('contact_person')->nullable()->after('shelter_name');
            $table->string('specialization')->nullable()->after('contact_person');
            $table->unsignedInteger('experience_years')->nullable()->after('specialization');
            $table->text('bio')->nullable()->after('experience_years');
            $table->json('available_slots')->nullable()->after('bio');
            $table->string('city')->nullable()->after('available_slots');
            $table->decimal('latitude', 10, 7)->nullable()->after('city');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });

        Schema::create('pets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->text('medical_history')->nullable();
            $table->string('photo_path')->nullable();
            $table->json('gallery')->nullable();
            $table->timestamps();
        });

        Schema::create('health_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vet_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('visit_date');
            $table->string('record_type')->default('treatment');
            $table->string('title');
            $table->text('diagnosis')->nullable();
            $table->text('treatment')->nullable();
            $table->text('symptoms')->nullable();
            $table->text('medications')->nullable();
            $table->text('lab_results')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            $table->timestamps();
        });

        Schema::create('insurance_policies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('provider_name');
            $table->string('policy_number');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->string('document_path')->nullable();
            $table->text('claim_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('vet_id')->constrained('users')->cascadeOnDelete();
            $table->dateTime('appointment_time');
            $table->string('status')->default('pending');
            $table->string('reason')->nullable();
            $table->text('owner_notes')->nullable();
            $table->text('vet_notes')->nullable();
            $table->timestamps();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->text('description')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('average_rating', 3, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->string('status')->default('cart');
            $table->timestamp('order_date')->nullable();
            $table->timestamps();
        });

        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('price_each', 10, 2);
            $table->timestamps();
        });

        Schema::create('adoption_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shelter_id')->constrained('users')->cascadeOnDelete();
            $table->string('pet_name');
            $table->string('species');
            $table->string('breed')->nullable();
            $table->unsignedInteger('age')->nullable();
            $table->string('gender')->nullable();
            $table->string('health_status')->nullable();
            $table->text('description')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('status')->default('available');
            $table->timestamps();
        });

        Schema::create('shelter_care_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('adoption_listings')->cascadeOnDelete();
            $table->string('care_type');
            $table->text('notes')->nullable();
            $table->timestamp('logged_at');
            $table->timestamps();
        });

        Schema::create('adoption_interests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('listing_id')->constrained('adoption_listings')->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->text('message')->nullable();
            $table->string('status')->default('pending');
            $table->text('shelter_response')->nullable();
            $table->timestamps();
        });

        Schema::create('care_articles', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('category');
            $table->string('content_type')->default('article');
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->string('video_url')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });

        Schema::create('feedbacks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('target_type');
            $table->unsignedBigInteger('target_id');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->timestamps();
            $table->index(['target_type', 'target_id']);
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('feedbacks');
        Schema::dropIfExists('care_articles');
        Schema::dropIfExists('adoption_interests');
        Schema::dropIfExists('shelter_care_logs');
        Schema::dropIfExists('adoption_listings');
        Schema::dropIfExists('order_items');
        Schema::dropIfExists('orders');
        Schema::dropIfExists('products');
        Schema::dropIfExists('appointments');
        Schema::dropIfExists('insurance_policies');
        Schema::dropIfExists('health_records');
        Schema::dropIfExists('pets');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'phone',
                'address',
                'shelter_name',
                'contact_person',
                'specialization',
                'experience_years',
                'bio',
                'available_slots',
                'city',
                'latitude',
                'longitude',
            ]);
        });
    }
};
