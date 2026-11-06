<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Category;
use App\Models\Destination;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DestinationController extends Controller
{
    public function index(Request $request): View
    {
        $destinations = Destination::query()
            ->with('category:id,name')
            ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', 'active')], 'price')
            ->withAvg('reviews as rating_avg', 'rating')
            ->when($request->filled('q'), fn ($q) => $q->search($request->string('q')->toString()))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderByDesc('id')
            ->paginate(10)
            ->withQueryString();

        return view('admin.destinasi.index', [
            'destinations' => $destinations,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('admin.destinasi.form', [
            'destinasi' => null,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['facilities'] = $this->facilityList($request->input('facilities'));
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('destinations', 'public');
        }

        Destination::create($data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('ok', 'Destinasi berhasil ditambahkan.');
    }

    public function edit(Destination $destination): View
    {
        return view('admin.destinasi.form', [
            'destinasi' => $destination,
            'categories' => Category::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug($data['name'], $destination->id);
        $data['facilities'] = $this->facilityList($request->input('facilities'));
        $data['is_featured'] = $request->boolean('is_featured');

        if ($request->hasFile('thumbnail')) {
            if ($destination->thumbnail) {
                Storage::disk('public')->delete($destination->thumbnail);
            }

            $data['thumbnail'] = $request->file('thumbnail')->store('destinations', 'public');
        }

        $destination->update($data);

        return redirect()
            ->route('admin.destinations.index')
            ->with('ok', 'Destinasi berhasil diperbarui.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        if (Booking::query()->where('destination_id', $destination->id)->exists()) {
            $destination->update(['status' => Destination::STATUS_INACTIVE]);

            return redirect()
                ->route('admin.destinations.index')
                ->with('ok', 'Destinasi dinonaktifkan karena sudah memiliki riwayat booking.');
        }

        if ($destination->thumbnail) {
            Storage::disk('public')->delete($destination->thumbnail);
        }

        $destination->delete();

        return redirect()
            ->route('admin.destinations.index')
            ->with('ok', 'Destinasi berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'description' => ['required', 'string'],
            'location' => ['required', 'string', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'opening_time' => ['nullable', 'date_format:H:i'],
            'closing_time' => ['nullable', 'date_format:H:i'],
            'facilities' => ['nullable', 'string', 'max:1000'],
            'is_featured' => ['nullable', 'boolean'],
            'status' => ['required', 'in:active,inactive'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Nama destinasi wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'description.required' => 'Deskripsi wajib diisi.',
            'location.required' => 'Lokasi wajib diisi.',
            'opening_time.date_format' => 'Format jam buka harus HH:MM.',
            'closing_time.date_format' => 'Format jam tutup harus HH:MM.',
            'status.required' => 'Status wajib dipilih.',
            'thumbnail.image' => 'Gambar harus berupa file gambar.',
            'thumbnail.mimes' => 'Format gambar harus JPG, PNG, atau WEBP.',
            'thumbnail.max' => 'Ukuran gambar maksimal 2 MB.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function facilityList(?string $input): array
    {
        return collect(explode(',', (string) $input))
            ->map(fn (string $facility) => trim($facility))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    protected function uniqueSlug(string $value, ?int $ignoreId = null): string
    {
        $base = Str::slug($value) ?: Str::random(8);
        $slug = $base;
        $i = 2;

        while (Destination::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
