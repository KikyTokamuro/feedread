<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feed extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'feeds';

    protected $fillable = [
        'user_id',
        'title',
        'url',
        'favicon_url',
        'last_refreshed_at',
        'last_error',
    ];

    protected $casts = [
        'last_refreshed_at' => 'datetime',
    ];

    /**
     * Owner of the feed.
     *
     * @return BelongsTo<User, Feed>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Articles fetched for this feed, newest first.
     *
     * @return HasMany<Item>
     */
    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    /**
     * Limit the query to the feeds owned by the given user.
     *
     * @param  Builder<Feed>  $query
     * @return Builder<Feed>
     */
    public function scopeOwnedBy(Builder $query, ?User $user): Builder
    {
        return $query->where('user_id', $user?->id);
    }
}
