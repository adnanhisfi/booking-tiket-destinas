<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Destination extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'location',
        'address',
        'latitude',
        'longitude',
        'opening_time',
        'closing_time',
        'facilities',
        'images',
        'thumbnail',
        'is_featured',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'facilities' => 'array',
            'images' => 'array',
            'is_featured' => 'boolean',
            'opening_time' => 'string',
            'closing_time' => 'string',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(TicketType::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeSearch($query, ?string $keyword)
    {
        if (! $keyword) {
            return $query;
        }

        return $query->where(function ($q) use ($keyword) {
            $q->where('name', 'like', "%{$keyword}%")
                ->orWhere('location', 'like', "%{$keyword}%")
                ->orWhere('description', 'like', "%{$keyword}%");
        });
    }

    public function minPrice(): ?float
    {
        return $this->ticketTypes()
            ->where('status', TicketType::STATUS_ACTIVE)
            ->min('price');
    }
}
