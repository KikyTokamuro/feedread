<?php

namespace App\Policies;

use App\Models\Feed;
use App\Models\User;

/**
 * A feed belongs to exactly one account and is invisible to everybody else.
 */
class FeedPolicy
{
    public function view(User $user, Feed $feed): bool
    {
        return $feed->user_id === $user->id;
    }

    public function update(User $user, Feed $feed): bool
    {
        return $this->view($user, $feed);
    }

    public function delete(User $user, Feed $feed): bool
    {
        return $this->view($user, $feed);
    }

    public function refresh(User $user, Feed $feed): bool
    {
        return $this->view($user, $feed);
    }
}
