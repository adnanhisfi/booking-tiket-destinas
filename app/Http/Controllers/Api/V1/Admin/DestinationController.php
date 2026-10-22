<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class DestinationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $destinations = Destination::query()
            ->with('category:id,name')
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->orderByDesc('id')
            ->paginate((int) $request->integer('per_page', 15));

        return response()->json([
            'data' => collect($destinations->items())->map(fn (Destination $d) => $this->payload($d)),
            'meta' => [
                'current_page' => $destinations->currentPage(),
                'last_page' => $destinations->lastPage(),
                'total' => $destinations->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        $data['slug'] = $this->uniqueSlug($data['slug'] ?? $data['name']);

        $destination = Destination::create($data);

        return response()->json([
            'message' => 'Destinasi dibuat.',
            'data' => $this->payload($destination->load('category')),
        ], 201);
    }

    public function show(Destination $destination): JsonResponse
    {
        return response()->json([
            'data' => array_merge($this->payload($destination->load(['category', 'ticketTypes'])), [
                'description' => $destination->description,
                'opening_time' => $destination->opening_time,
                'closing_time' => $destination->closing_time,
                'facilities' => $destination->facilities ?? [],
                'images' => $destination->images ?? [],
                'ticket_types' => $destination->ticketTypes->map(fn ($t) => [
                    'id' => $t->id,
                    'name' => $t->name,
                    'price' => (float) $t->price,
                    'default_quota' => $t->default_quota,
                    'status' => $t->status,
                ]),
            ]),
        ]);
    }

    public function update(Request $request, Destination $destination): JsonResponse
    {
        $data = $this->validated($request, $destination);

        if (isset($data['slug'])) {
            $data['slug'] = $this->uniqueSlug($data['slug'], $destination->id);
        }

        $destination->update($data);

        return response()->json([
            'message' => 'Destinasi diperbarui.',
            'data' => $this->payload($destination->refresh()->load('category')),
        ]);
    }

    public function destroy(Destination $destination): JsonResponse
    {
        $destination->delete();

        return response()->json(['message' => 'Destinasi dihapus.']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request, ?Destination $destination = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:160'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'facilities' => ['nullable', 'array'],
            'facilities.*' => ['string', 'max:100'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:255'],
            'thumbnail' => ['nullable', 'string', 'max:255'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['nullable', Rule::in([Destination::STATUS_ACTIVE, Destination::STATUS_INACTIVE])],
        ]);
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while (Destination::where('slug', $slug)->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(Destination $destination): array
    {
        return [
            'id' => $destination->id,
            'name' => $destination->name,
            'slug' => $destination->slug,
            'location' => $destination->location,
            'status' => $destination->status,
            'is_featured' => (bool) $destination->is_featured,
            'category' => $destination->category ? [
                'id' => $destination->category->id,
                'name' => $destination->category->name,
            ] : null,
        ];
    }
}
