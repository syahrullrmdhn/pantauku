<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['app_open', 'browser_access']);
            $table->string('value', 255);
            $table->boolean('is_suspicious')->default(false);
            $table->string('device_id', 100)->nullable();
            $table->timestamp('occurred_at');
            $table->timestamp('created_at')->useCurrent();
            
            $table->index('type');
            $table->index('is_suspicious');
            $table->index('occurred_at');
            $table->index('device_id');
        });

        Schema::create('blacklist_domains', function (Blueprint $table) {
            $table->id();
            $table->string('domain', 255)->unique();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value')->nullable();
            $table->timestamp('updated_at')->useCurrent()->useCurrentOnUpdate();
        });

        // Seed default admin user
        DB::table('users')->insert([
            'name' => 'Syahrul',
            'email' => 'syahrul@example.com',
            'password' => Hash::make('ngd987hj56'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Seed default blacklist
        $domains = [
            ['domain' => 'mpo777.com', 'notes' => 'Situs judi online'],
            ['domain' => 'slot88.com', 'notes' => 'Situs judi slot'],
            ['domain' => 'poker88.com', 'notes' => 'Poker online'],
            ['domain' => 'togel.com', 'notes' => 'Togel online'],
            ['domain' => 'sbobet.com', 'notes' => 'Sports betting'],
            ['domain' => 'maxbet.com', 'notes' => 'Casino online'],
            ['domain' => 'judi123.com', 'notes' => 'Situs judi'],
            ['domain' => 'qqslot.com', 'notes' => 'Slot online'],
            ['domain' => 'bola88.com', 'notes' => 'Judi bola'],
            ['domain' => 'idnplay.com', 'notes' => 'IDN Play'],
            ['domain' => 'joker123.com', 'notes' => 'Joker slot'],
            ['domain' => 'cmd368.com', 'notes' => 'Sportsbook'],
            ['domain' => 'dewa poker.com', 'notes' => 'Poker judi'],
            ['domain' => 'ion casino.com', 'notes' => 'Casino'],
            ['domain' => 'casino online.com', 'notes' => 'Casino'],
        ];
        DB::table('blacklist_domains')->insert($domains);

        // Default settings
        DB::table('settings')->insert([
            ['key' => 'telegram_bot_token', 'value' => ''],
            ['key' => 'telegram_chat_id', 'value' => ''],
            ['key' => 'api_token', 'value' => 'pantauku_api_token_2026_secure_random_key'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('events');
        Schema::dropIfExists('blacklist_domains');
        Schema::dropIfExists('settings');
    }
};
