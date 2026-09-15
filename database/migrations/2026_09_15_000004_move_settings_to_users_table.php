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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('dark')->default(false)->after('is_admin');
        });

        $this->copyDarkSettingToUsers();

        // Nothing reads the instance wide settings any more.
        Schema::dropIfExists('settings');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('dark');
        });
    }

    /**
     * Everyone who had switched the dark scheme on keeps it.
     */
    private function copyDarkSettingToUsers(): void
    {
        if (! Schema::hasTable('settings')) {
            return;
        }

        $payload = DB::table('settings')
            ->where('group', 'general')
            ->where('name', 'dark')
            ->value('payload');

        if ($payload === null) {
            return;
        }

        DB::table('users')->update(['dark' => (int) (json_decode((string) $payload, true) === true)]);
    }
};
