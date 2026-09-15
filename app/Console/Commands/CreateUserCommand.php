<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\UserService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'feedread:user
                            {email : Email address of the new account}
                            {--name= : Display name (defaults to the part before the @)}
                            {--password= : Password, generated when omitted}
                            {--admin : Grant administrator rights}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a FeedRead account';

    /**
     * Execute the console command.
     */
    public function handle(UserService $userService): int
    {
        $email = Str::lower(trim((string) $this->argument('email')));

        if (User::query()->where('email', $email)->exists()) {
            $this->error("An account for {$email} already exists.");

            return self::FAILURE;
        }

        $generated = $this->option('password') === null;

        $result = $userService->create([
            'name' => $this->option('name') ?: Str::before($email, '@'),
            'email' => $email,
            'password' => $this->option('password'),
            'is_admin' => (bool) $this->option('admin'),
        ]);

        $this->info(sprintf(
            'Created %s%s.',
            $result['user']->email,
            $result['user']->is_admin ? ' (administrator)' : ''
        ));

        if ($generated) {
            $this->line("Generated password: {$result['password']}");
        }

        // The first account adopts feeds that were created before feeds had
        // owners, so an existing install is not left with hidden subscriptions.
        if (User::query()->count() === 1) {
            $claimed = $userService->claimOrphanFeeds($result['user']);

            if ($claimed > 0) {
                $this->line("Assigned {$claimed} existing feed(s) to this account.");
            }
        }

        return self::SUCCESS;
    }
}
