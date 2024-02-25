<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use AshAllenDesign\FaviconFetcher\Facades\Favicon;

class Feed extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'feeds';
    protected $fillable = ['title', 'url'];

    public function favicon(): ?string
    {
        $favicon = Favicon::fetch($this->url)->cache(now()->addWeek());
        return $favicon->getFaviconUrl();
    }
}
