<?php

namespace App\Models;

use App\Enums\City;
use App\Enums\CocokUntuk;
use App\Enums\Duration;
use App\Enums\IndoorOutdoor;
use App\Enums\PriceRange;
use App\Enums\ReviewStatus;
use App\Enums\Status;
use App\Enums\WaktuIdeal;
use App\Enums\Zone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Destination extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description_short',
        'description_long',
        'address',
        'latitude',
        'longitude',
        'opening_hours',
        'price_info',
        'contact_phone',
        'contact_instagram',
        'contact_website',
        'price_range',
        'zone',
        'city',
        'indoor_outdoor',
        'duration',
        'cocok_untuk',
        'waktu_ideal',
        'status',
        'rating_cache',
        'review_count_cache',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'opening_hours' => 'array',
            'price_range' => PriceRange::class,
            'zone' => Zone::class,
            'city' => City::class,
            'indoor_outdoor' => IndoorOutdoor::class,
            'duration' => Duration::class,
            'cocok_untuk' => AsEnumCollection::of(CocokUntuk::class),
            'waktu_ideal' => AsEnumCollection::of(WaktuIdeal::class),
            'status' => Status::class,
            'rating_cache' => 'decimal:2',
            'review_count_cache' => 'integer',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /** First gallery image URL, or null when the destination has no images. */
    public function coverUrl(): ?string
    {
        return $this->images->first()?->url;
    }

    // -- Relationships --------------------------------------------------

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(DestinationImage::class)->orderBy('sort_order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'destination_tag');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function publishedReviews(): HasMany
    {
        return $this->reviews()->where('status', ReviewStatus::Published);
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'favorites')->withTimestamps();
    }

    // -- Scopes ---------------------------------------------------------

    /** Only published (aktif) destinations — what the public should see. */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', Status::Aktif);
    }

    // -- Derived rating cache ------------------------------------------

    /**
     * Recompute rating_cache & review_count_cache from published reviews.
     */
    public function recalculateRating(): void
    {
        $published = $this->publishedReviews();

        $this->forceFill([
            'review_count_cache' => $published->count(),
            'rating_cache' => $published->avg('rating'),
        ])->save();
    }
}
