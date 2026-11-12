<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pets', function (Blueprint $table) {
            $table->string('public_token', 64)->nullable()->unique()->after('id');
            $table->json('public_fields')->nullable()->after('public_token');
            $table->boolean('is_lost')->default(false)->after('public_fields');
            $table->text('lost_note')->nullable()->after('is_lost');
            $table->timestamp('lost_at')->nullable()->after('lost_note');
        });

        Schema::create('pet_sightings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained()->cascadeOnDelete();
            $table->string('finder_name')->nullable();
            $table->text('message');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->timestamps();
        });

        $defaults = json_encode([
            'photo' => false,
            'name' => false,
            'species' => false,
            'breed' => false,
            'allergies' => false,
            'medications' => false,
            'phone' => false,
            'whatsapp' => false,
        ]);

        DB::table('pets')->orderBy('id')->pluck('id')->each(function ($id) use ($defaults): void {
            do {
                $token = strtolower(Str::random(40));
            } while (DB::table('pets')->where('public_token', $token)->exists());

            DB::table('pets')->where('id', $id)->update([
                'public_token' => $token,
                'public_fields' => $defaults,
            ]);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_sightings');

        Schema::table('pets', function (Blueprint $table) {
            $table->dropUnique(['public_token']);
            $table->dropColumn(['public_token', 'public_fields', 'is_lost', 'lost_note', 'lost_at']);
        });
    }
};
