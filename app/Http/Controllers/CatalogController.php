<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Halaman katalog publik: destinasi & harga dibaca dinamis dari database.
     */
    public function beranda(Request $request): View
    {
        $categories = Category::cachedList();

        $keyword = $this->queryString($request, 'q');
        $categorySlug = $this->queryString($request, 'category');

        $destinations = Destination::query()
            ->active()
            ->with('category:id,name,slug')
            ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', 'active')], 'price')
            ->withAvg('reviews as rating_avg', 'rating')
            ->search($keyword)
            ->when($categorySlug !== null, fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $categorySlug)
            ))
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->limit(8)
            ->get();

        return view('pengunjung.beranda', compact('destinations', 'categories'));
    }

    /**
     * Daftar seluruh destinasi dengan pencarian, filter kategori/lokasi/harga, dan urutan.
     */
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'kategori' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:featured,price_asc,price_desc,name'],
        ]);

        $categories = Category::cachedList();

        $category = $filters['kategori'] ?? $filters['category'] ?? null;
        $location = $filters['location'] ?? null;
        $minPrice = $filters['min_price'] ?? null;
        $maxPrice = $filters['max_price'] ?? null;
        $sort = $filters['sort'] ?? 'featured';

        $destinations = Destination::query()
            ->active()
            ->with('category:id,name,slug')
            ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', 'active')], 'price')
            ->withAvg('reviews as rating_avg', 'rating')
            ->search($filters['q'] ?? null)
            ->when($category, fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $category)
            ))
            ->when($location, fn ($q) => $q->where('location', 'like', "%{$location}%"))
            ->when($minPrice !== null, fn ($q) => $q->whereHas(
                'ticketTypes',
                fn ($t) => $t->where('status', 'active')->where('price', '>=', $minPrice)
            ))
            ->when($maxPrice !== null, fn ($q) => $q->whereHas(
                'ticketTypes',
                fn ($t) => $t->where('status', 'active')->where('price', '<=', $maxPrice)
            ))
            ->when($sort === 'price_asc', fn ($q) => $q->orderBy('min_price'))
            ->when($sort === 'price_desc', fn ($q) => $q->orderByDesc('min_price'))
            ->when($sort === 'name', fn ($q) => $q->orderBy('name'))
            ->when(! in_array($sort, ['price_asc', 'price_desc', 'name'], true), fn ($q) => $q
                ->orderByDesc('is_featured')
                ->orderByDesc('id'))
            ->paginate(12)
            ->withQueryString();

        return view('pengunjung.destinasi.index', compact(
            'destinations',
            'categories',
            'category',
            'location',
            'minPrice',
            'maxPrice',
            'sort',
        ));
    }

    /**
     * Halaman detail destinasi beserta jenis tiket dan ulasan.
     */
    public function detail(Request $request, Destination $destination): View
    {
        abort_if($destination->status !== Destination::STATUS_ACTIVE, 404);

        $destination->load([
            'category:id,name,slug',
            'ticketTypes' => fn ($q) => $q->where('status', 'active')->orderBy('price'),
            'reviews' => fn ($q) => $q->with('user:id,name')->latest()->limit(20),
        ]);

        // Satu query agregat untuk rating & jumlah ulasan (menghindari dua query terpisah).
        $reviewStats = $destination->reviews()
            ->selectRaw('AVG(rating) as rating_avg, COUNT(*) as reviews_count')
            ->first();

        $destination->setAttribute('rating_avg', round((float) ($reviewStats->rating_avg ?? 0), 1));
        $destination->setAttribute('reviews_count', (int) ($reviewStats->reviews_count ?? 0));
        $destination->setAttribute('min_price', $destination->ticketTypes->min('price'));

        $user = $request->user();
        $hasReviewed = false;
        $canReview = false;

        if ($user && $user->role === User::ROLE_CUSTOMER) {
            $hasReviewed = Review::query()
                ->where('user_id', $user->id)
                ->where('destination_id', $destination->id)
                ->exists();

            $canReview = ! $hasReviewed && Booking::query()
                ->where('user_id', $user->id)
                ->where('destination_id', $destination->id)
                ->whereIn('status', [Booking::STATUS_PAID, Booking::STATUS_COMPLETED])
                ->exists();
        }

        return view('pengunjung.destinasi.show', compact(
            'destination',
            'user',
            'hasReviewed',
            'canReview',
        ));
    }
}
