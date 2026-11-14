@extends('layouts.app')
@section('title', $destination->name.' - JelajahKu')
@section('hideNav', '1')

@php
    $user = auth()->user();
    $placeholder = asset('images/destination-placeholder.svg');
    $img = $destination->thumbnailUrl() ?? $placeholder;
    $rating = (float) ($destination->rating_avg ?? 0);
    $minPrice = $destination->min_price;
    $canBook = ! $user || $user->role === \App\Models\User::ROLE_CUSTOMER;
@endphp

@section('content')
<div class="pb-24">
    <div class="relative">
        <img src="{{ $img }}" class="w-full h-56 object-cover" alt="{{ $destination->name }}"
             onerror="this.onerror=null;this.src='{{ $placeholder }}'">
        <a href="{{ route('katalog.index') }}" class="absolute top-4 left-4 w-9 h-9 rounded-full bg-white grid place-items-center shadow">‹</a>
    </div>

    <div class="p-4 -mt-4 bg-white rounded-t-3xl relative">
        <div class="flex items-start justify-between gap-2">
            <div>
                <h1 class="text-lg font-bold">{{ $destination->name }}</h1>
                <p class="text-xs text-gray-500">
                    ★ {{ $rating > 0 ? number_format($rating, 1) : 'Baru' }}
                    ({{ $destination->reviews_count }} ulasan) · {{ $destination->location }}
                </p>
            </div>
            @if ($destination->category)
                <span class="text-[10px] px-2 py-1 rounded-full bg-soft text-primary shrink-0">{{ $destination->category->name }}</span>
            @endif
        </div>

        @if ($destination->opening_time || $destination->address)
            <div class="mt-3 rounded-xl bg-gray-50 p-3 text-xs text-gray-600 space-y-1">
                @if ($destination->opening_time && $destination->closing_time)
                    <p>🕐 Buka {{ substr($destination->opening_time, 0, 5) }} – {{ substr($destination->closing_time, 0, 5) }}</p>
                @endif
                @if ($destination->address)
                    <p>📍 {{ $destination->address }}</p>
                @endif
            </div>
        @endif

        <p class="font-semibold text-sm mt-4">Tentang Destinasi</p>
        <p class="text-sm text-gray-600 mt-1">{{ $destination->description }}</p>

        @if (! empty($destination->facilities))
            <p class="font-semibold text-sm mt-4">Fasilitas</p>
            <div class="grid grid-cols-2 gap-2 mt-2 text-sm">
                @foreach ($destination->facilities as $facility)
                    <span class="flex items-center gap-2"><span class="w-4 h-4 rounded border border-primary bg-soft"></span>{{ $facility }}</span>
                @endforeach
            </div>
        @endif

        <p class="font-semibold text-sm mt-4">Jenis Tiket</p>
        <div class="mt-2 space-y-2">
            @forelse ($destination->ticketTypes as $type)
                <div class="flex items-center justify-between border rounded-xl p-3">
                    <div>
                        <p class="text-sm font-medium">{{ $type->name }}</p>
                        <p class="text-[11px] text-gray-500">{{ $type->description ?: 'Kuota harian '.$type->default_quota.' tiket' }}</p>
                    </div>
                    <p class="text-sm font-semibold text-primary">Rp {{ number_format((float) $type->price, 0, ',', '.') }}</p>
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada jenis tiket untuk destinasi ini.</p>
            @endforelse
        </div>

        <p class="font-semibold text-sm mt-4">Ulasan Pengunjung</p>
        <div class="mt-2 space-y-3">
            @forelse ($destination->reviews as $review)
                <div class="border-b pb-3 last:border-0">
                    <p class="text-xs text-amber-500">{{ str_repeat('★', max(0, min(5, (int) $review->rating))) }}{{ str_repeat('☆', 5 - max(0, min(5, (int) $review->rating))) }}</p>
                    <p class="text-sm font-medium">{{ $review->user?->name ?? 'Pengunjung' }}</p>
                    @if ($review->comment)
                        <p class="text-xs text-gray-500">{{ $review->comment }}</p>
                    @endif
                </div>
            @empty
                <p class="text-sm text-gray-500">Belum ada ulasan.</p>
            @endforelse
        </div>

        @if ($canReview)
            <div class="mt-4 rounded-xl border p-3">
                <p class="font-semibold text-sm">Berikan Ulasan</p>
                <p class="text-[11px] text-gray-500 mt-0.5">Bagikan pengalamanmu setelah berkunjung.</p>
                <form method="POST" action="{{ route('review.store', $destination->slug) }}" class="mt-3 space-y-2">
                    @csrf
                    <select name="rating" required class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50">
                        <option value="">Pilih rating bintang...</option>
                        @for ($i = 5; $i >= 1; $i--)
                            <option value="{{ $i }}" @selected((string) old('rating') === (string) $i)>
                                {{ str_repeat('★', $i) }} ({{ $i }})
                            </option>
                        @endfor
                    </select>
                    <textarea name="comment" rows="3" maxlength="1000" placeholder="Ceritakan pengalamanmu (opsional)"
                              class="w-full border rounded-lg px-3 py-2.5 text-sm bg-gray-50">{{ old('comment') }}</textarea>
                    <button class="w-full bg-primary text-white rounded-xl py-2.5 text-sm font-semibold">Kirim Ulasan</button>
                </form>
            </div>
        @elseif ($hasReviewed)
            <p class="mt-4 text-xs text-gray-400">Kamu sudah memberi ulasan untuk destinasi ini. Terima kasih!</p>
        @elseif (! $user)
            <p class="mt-4 text-xs text-gray-400">
                <a href="{{ route('login') }}" class="text-primary font-semibold">Masuk</a> untuk memberi ulasan setelah berkunjung.
            </p>
        @elseif ($user->role === \App\Models\User::ROLE_CUSTOMER)
            <p class="mt-4 text-xs text-gray-400">Ulasan dapat diberikan setelah pembayaran booking di destinasi ini lunas.</p>
        @endif
    </div>

    <div class="fixed bottom-0 inset-x-0 max-w-md mx-auto bg-white border-t p-4 flex items-center gap-4">
        <div>
            <p class="text-[11px] text-gray-400">Harga Tiket</p>
            <p class="font-bold text-primary">
                @if ($minPrice !== null)
                    Rp {{ number_format((float) $minPrice, 0, ',', '.') }}
                    <span class="text-xs font-normal text-gray-400">/orang</span>
                @else
                    -
                @endif
            </p>
        </div>
        @if ($canBook && $destination->ticketTypes->isNotEmpty())
            <a href="{{ route('booking.create', $destination->slug) }}" class="flex-1 text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Booking Sekarang</a>
        @elseif ($destination->ticketTypes->isEmpty())
            <span class="flex-1 text-center bg-gray-100 text-gray-400 rounded-xl py-3 text-sm font-semibold">Tiket Belum Tersedia</span>
        @else
            <a href="{{ route('admin.dashboard') }}" class="flex-1 text-center bg-primary text-white rounded-xl py-3 text-sm font-semibold">Buka Admin Panel</a>
        @endif
    </div>
</div>
@endsection
