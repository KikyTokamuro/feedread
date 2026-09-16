<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Never touch an install that already has accounts.
        if (User::query()->exists()) {
            return;
        }

        $userService = app(UserService::class);

        $result = $userService->create([
            'name' => config('feedread.admin.name'),
            'email' => config('feedread.admin.email'),
            'password' => config('feedread.admin.password'),
            'is_admin' => true,
        ]);

        $this->command?->info(sprintf('Created administrator %s.', $result['user']->email));

        if (config('feedread.admin.password')) {
            $this->command?->line('Password taken from FEEDREAD_ADMIN_PASSWORD.');
        } else {
            $this->command?->line('WARNING: store this password now, it will not be shown again.');
            $this->command?->line("Generated password: {$result['password']}");
        }

        $claimed = $userService->claimOrphanFeeds($result['user']);

        if ($claimed > 0) {
            $this->command?->info("Assigned {$claimed} existing feed(s) to this account.");
        }
    }
}
