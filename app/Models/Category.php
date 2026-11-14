<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasFactory;

    public const CACHE_KEY = 'catalog.categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Daftar kategori untuk halaman katalog, di-cache satu jam.
     *
     * @return Collection<int, Category>
     */
    public static function cachedList(): Collection
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHour(),
            fn () => self::query()->orderBy('name')->get(['id', 'name', 'slug'])
        );
    }

    public function destinations(): HasMany
    {
        return $this->hasMany(Destination::class);
    }
}
