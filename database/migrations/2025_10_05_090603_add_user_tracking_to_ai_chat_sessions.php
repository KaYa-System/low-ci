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
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('last_activity');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('country', 5)->nullable()->after('user_agent');
            $table->string('country_name', 100)->nullable()->after('country');
            $table->string('city', 100)->nullable()->after('country_name');
            $table->string('device_type', 50)->nullable()->after('city'); // desktop, mobile, tablet
            $table->string('browser', 50)->nullable()->after('device_type');
            $table->string('browser_version', 20)->nullable()->after('browser');
            $table->string('operating_system', 50)->nullable()->after('browser_version');
            $table->string('platform', 50)->nullable()->after('operating_system'); // iOS, Android, Windows, macOS, Linux
            $table->boolean('is_mobile')->default(false)->after('platform');
            $table->boolean('is_tablet')->default(false)->after('is_mobile');
            $table->boolean('is_desktop')->default(true)->after('is_tablet');
            $table->string('language', 10)->nullable()->after('is_desktop');
            $table->string('timezone', 50)->nullable()->after('language');
            $table->integer('screen_width')->nullable()->after('timezone');
            $table->integer('screen_height')->nullable()->after('screen_width');
            
            // Index pour les requêtes analytics
            $table->index(['country', 'created_at']);
            $table->index(['device_type', 'created_at']);
            $table->index(['browser', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ai_chat_sessions', function (Blueprint $table) {
            $table->dropIndex(['country', 'created_at']);
            $table->dropIndex(['device_type', 'created_at']);
            $table->dropIndex(['browser', 'created_at']);
            
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'country',
                'country_name',
                'city',
                'device_type',
                'browser',
                'browser_version',
                'operating_system',
                'platform',
                'is_mobile',
                'is_tablet',
                'is_desktop',
                'language',
                'timezone',
                'screen_width',
                'screen_height'
            ]);
        });
    }
};
