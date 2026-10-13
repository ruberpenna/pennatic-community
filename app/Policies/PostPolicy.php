<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    /**
     * Admins may moderate everything (including in the admin panel); voting follows the normal rule.
     */
    public function before(User $user, string $ability): ?bool
    {
        return $user->isAdmin() && $ability !== 'vote' ? true : null;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->id === $post->user_id || $user->isAdmin();
    }

    public function vote(User $user, Post $post): bool
    {
        return $user->id !== $post->user_id;
    }

    public function pin(User $user, Post $post): bool
    {
        return $user->isAdmin();
    }
}
