<?php

namespace App\Services;

use App\Models\Feed;
use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create an account.
     *
     * When no password is supplied one is generated, so an admin can hand over
     * a one time password instead of inventing one.
     *
     * @param  array{name: string, email: string, password?: string|null, is_admin?: bool}  $attributes
     * @return array{user: User, password: string}
     */
    public function create(array $attributes): array
    {
        $password = $attributes['password'] ?: Str::password(16);

        $user = User::create([
            'name' => $attributes['name'],
            'email' => Str::lower($attributes['email']),
            'password' => $password,
            'is_admin' => (bool) ($attributes['is_admin'] ?? false),
        ]);

        return ['user' => $user, 'password' => $password];
    }

    /**
     * Give feeds that have no owner yet to the given account.
     *
     * Feeds created before FeedRead knew about accounts end up unowned; this
     * keeps them reachable for the first admin instead of hiding them.
     *
     * @return int Number of feeds claimed.
     */
    public function claimOrphanFeeds(User $user): int
    {
        return Feed::withTrashed()->whereNull('user_id')->update(['user_id' => $user->id]);
    }
}
