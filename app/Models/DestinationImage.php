<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DestinationImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'destination_id',
        'path',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    /**
     * Root-relative public URL. Images live directly under public/ (path is
     * stored relative to the public root, e.g. "images/destinations/foo.jpg")
     * so they are served without the storage symlink, and the relative URL
     * follows whatever host:port the app runs on.
     */
    public function getUrlAttribute(): string
    {
        return '/'.ltrim($this->path, '/');
    }
}
