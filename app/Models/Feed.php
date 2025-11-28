<?php

namespace App\Models;

use App\Services\FeedService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feed extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'feeds';
    protected $fillable = ['title', 'url'];

    public function favicon(): ?string
    {
        return FeedService::getFaviconUrl($this);
    }
}
