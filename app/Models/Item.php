<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Item extends Model
{
    use HasFactory;

    protected $table = 'items';

    protected $fillable = [
        'feed_id',
        'guid',
        'guid_hash',
        'title',
        'url',
        'author',
        'content',
        'published_at',
        'read_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    /**
     * Keep the lookup hash in sync with the feed's own item id.
     *
     * FeedService writes it explicitly because upsert() bypasses model
     * events; this covers every other way an item can be created.
     */
    protected static function booted(): void
    {
        static::saving(function (Item $item) {
            if ($item->isDirty('guid') || blank($item->guid_hash)) {
                $item->guid_hash = sha1((string) $item->guid);
            }
        });
    }

    /**
     * Feed this article belongs to.
     *
     * @return BelongsTo<Feed, Item>
     */
    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * Is the article still unread?
     */
    public function isUnread(): bool
    {
        return $this->read_at === null;
    }

    /**
     * Plain text teaser of the article body.
     */
    public function excerpt(int $limit = 1000): string
    {
        // Strip the markup *before* truncating, otherwise the limit is spent on
        // tags and can cut through the middle of an entity.
        return Str::limit(trim(strip_tags($this->content ?? '')), $limit);
    }

    /**
     * Human readable title, falling back to the URL when the feed omits one.
     */
    public function displayTitle(): string
    {
        return $this->title ?: ($this->url ?: 'Untitled');
    }

    /**
     * Limit the query to unread articles.
     *
     * @param  Builder<Item>  $query
     * @return Builder<Item>
     */
    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }
}
