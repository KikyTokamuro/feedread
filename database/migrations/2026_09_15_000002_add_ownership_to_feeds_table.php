<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
            $table->string('favicon_url')->nullable()->after('url');
            $table->timestamp('last_refreshed_at')->nullable()->after('favicon_url');
            $table->text('last_error')->nullable()->after('last_refreshed_at');
        });

        // Feeds created before this migration had no owner. Hand them to the
        // oldest account so the existing subscriptions are not lost.
        $firstUserId = DB::table('users')->orderBy('id')->value('id');

        if ($firstUserId) {
            DB::table('feeds')->whereNull('user_id')->update(['user_id' => $firstUserId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feeds', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
            $table->dropColumn(['favicon_url', 'last_refreshed_at', 'last_error']);
        });
    }
};
