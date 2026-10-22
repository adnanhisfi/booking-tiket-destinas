<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Services\QuotaService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DestinationController extends Controller
{
    public function __construct(protected QuotaService $quota) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'location' => ['nullable', 'string', 'max:100'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'featured' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:name,price_asc,price_desc,newest'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $query = Destination::query()
            ->active()
            ->with(['category:id,name,slug'])
            ->withAvg('reviews', 'rating')
            ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', TicketType::STATUS_ACTIVE)], 'price')
            ->search($filters['q'] ?? null)
            ->when($filters['location'] ?? null, fn ($q, $location) => $q->where('location', 'like', "%{$location}%"))
            ->when($filters['category'] ?? null, fn ($q, $category) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $category)->orWhere('name', $category)
            ))
            ->when($filters['featured'] ?? null, fn ($q, $featured) => $q->where('is_featured', (bool) $featured))
            ->when($filters['min_price'] ?? null, fn ($q, $min) => $q->whereHas(
                'ticketTypes',
                fn ($t) => $t->where('price', '>=', $min)
            ))
            ->when($filters['max_price'] ?? null, fn ($q, $max) => $q->whereHas(
                'ticketTypes',
                fn ($t) => $t->where('price', '<=', $max)
            ));

        $query = match ($filters['sort'] ?? 'newest') {
            'name' => $query->orderBy('name'),
            'price_asc' => $query->orderBy('min_price'),
            'price_desc' => $query->orderByDesc('min_price'),
            default => $query->orderByDesc('is_featured')->orderByDesc('id'),
        };

        $destinations = $query->paginate($filters['per_page'] ?? 12)->withQueryString();

        return response()->json([
            'data' => collect($destinations->items())->map(fn (Destination $d) => $this->summary($d)),
            'meta' => [
                'current_page' => $destinations->currentPage(),
                'per_page' => $destinations->perPage(),
                'total' => $destinations->total(),
                'last_page' => $destinations->lastPage(),
            ],
        ]);
    }

    public function show(Destination $destination): JsonResponse
    {
        abort_if($destination->status !== Destination::STATUS_ACTIVE, 404);

        $destination->load([
            'category:id,name,slug',
            'ticketTypes' => fn ($q) => $q->where('status', TicketType::STATUS_ACTIVE),
            'reviews' => fn ($q) => $q->with('user:id,name')->latest()->limit(20),
        ]);

        return response()->json([
            'data' => array_merge($this->summary($destination), [
                'description' => $destination->description,
                'address' => $destination->address,
                'latitude' => $destination->latitude,
                'longitude' => $destination->longitude,
                'opening_time' => $destination->opening_time,
                'closing_time' => $destination->closing_time,
                'facilities' => $destination->facilities ?? [],
                'images' => $destination->images ?? [],
                'ticket_types' => $destination->ticketTypes->map(fn (TicketType $t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'description' => $t->description,
                    'price' => (float) $t->price,
                    'default_quota' => $t->default_quota,
                ]),
                'reviews' => $destination->reviews->map(fn ($r) => [
                    'id' => $r->id,
                    'rating' => $r->rating,
                    'comment' => $r->comment,
                    'user' => $r->user?->name,
                    'created_at' => $r->created_at?->toIso8601String(),
                ]),
            ]),
        ]);
    }

    public function tickets(Request $request, Destination $destination): JsonResponse
    {
        abort_if($destination->status !== Destination::STATUS_ACTIVE, 404);

        $data = $request->validate([
            'date' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $date = Carbon::parse($data['date'] ?? now()->toDateString())->startOfDay();
        $maxDays = (int) config('payment.max_advance_days');

        if ($date->lt(today()) || $date->gt(today()->addDays($maxDays))) {
            throw ValidationException::withMessages([
                'date' => "Tanggal harus antara hari ini dan {$maxDays} hari ke depan.",
            ]);
        }

        $ticketTypes = $destination->ticketTypes()
            ->where('status', TicketType::STATUS_ACTIVE)
            ->get();

        $existing = TicketAvailability::query()
            ->whereIn('ticket_type_id', $ticketTypes->pluck('id'))
            ->whereDate('visit_date', $date)
            ->get()
            ->keyBy('ticket_type_id');

        return response()->json([
            'data' => [
                'destination_id' => $destination->id,
                'date' => $date->toDateString(),
                'ticket_types' => $ticketTypes->map(function (TicketType $type) use ($existing) {
                    $row = $existing->get($type->id);
                    $quota = $row?->quota ?? $type->default_quota;
                    $sold = $row?->sold ?? 0;

                    return [
                        'id' => $type->id,
                        'name' => $type->name,
                        'description' => $type->description,
                        'price' => (float) $type->price,
                        'quota' => $quota,
                        'sold' => $sold,
                        'remaining' => max(0, $quota - $sold),
                        'available' => ($quota - $sold) > 0,
                    ];
                }),
            ],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(Destination $destination): array
    {
        return [
            'id' => $destination->id,
            'name' => $destination->name,
            'slug' => $destination->slug,
            'location' => $destination->location,
            'thumbnail' => $destination->thumbnail,
            'is_featured' => (bool) $destination->is_featured,
            'category' => $destination->category ? [
                'id' => $destination->category->id,
                'name' => $destination->category->name,
                'slug' => $destination->category->slug,
            ] : null,
            'min_price' => $destination->min_price !== null ? (float) $destination->min_price : null,
            'rating' => $destination->reviews_avg_rating !== null ? round((float) $destination->reviews_avg_rating, 2) : null,
        ];
    }
}
