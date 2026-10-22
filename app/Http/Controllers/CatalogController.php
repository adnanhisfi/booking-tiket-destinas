<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Destination;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController extends Controller
{
    /**
     * Halaman katalog publik: destinasi & harga dibaca dinamis dari database.
     */
    public function beranda(Request $request): View
    {
        $categories = Category::query()->orderBy('name')->get(['id', 'name', 'slug']);

        $destinations = Destination::query()
            ->active()
            ->with('category:id,name,slug')
            ->withMin(['ticketTypes as min_price' => fn ($q) => $q->where('status', 'active')], 'price')
            ->search($request->input('q'))
            ->when($request->filled('category'), fn ($q) => $q->whereHas(
                'category',
                fn ($c) => $c->where('slug', $request->input('category'))
            ))
            ->orderByDesc('is_featured')
            ->orderByDesc('id')
            ->get();

        return view('katalog.beranda', compact('destinations', 'categories'));
    }

    /**
     * Halaman detail destinasi beserta jenis tiket dan ulasan.
     */
    public function detail(Destination $destination): View
    {
        abort_if($destination->status !== Destination::STATUS_ACTIVE, 404);

        $destination->load([
            'category:id,name,slug',
            'ticketTypes' => fn ($q) => $q->where('status', 'active'),
            'reviews' => fn ($q) => $q->with('user:id,name')->latest()->limit(20),
        ]);

        return view('katalog.detail', compact('destination'));
    }
}
