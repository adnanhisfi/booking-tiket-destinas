<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $destination->name }} — JelajahKu</title>
    <style>
        :root { --brand: #0f766e; --brand-dark: #115e59; --ink: #1f2937; --muted: #6b7280; --line: #e5e7eb; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: var(--ink); background: #f9fafb; }
        header { background: var(--brand); color: #fff; padding: 20px 24px; }
        header .wrap { max-width: 900px; margin: 0 auto; }
        header a { color: #ccfbf1; text-decoration: none; font-size: 14px; }
        header h1 { margin: 8px 0 4px; font-size: 26px; }
        main { max-width: 900px; margin: 0 auto; padding: 24px; }
        .panel { background: #fff; border: 1px solid var(--line); border-radius: 12px; padding: 20px; margin-bottom: 20px; }
        .panel h2 { margin: 0 0 12px; font-size: 18px; }
        .meta { color: var(--muted); font-size: 14px; margin: 0 0 12px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { text-align: left; padding: 10px 12px; border-bottom: 1px solid var(--line); font-size: 14px; }
        th { color: var(--muted); font-weight: 600; }
        .price { font-weight: 700; color: var(--brand-dark); }
        .review { border-bottom: 1px solid var(--line); padding: 10px 0; font-size: 14px; }
        .review:last-child { border-bottom: 0; }
        .stars { color: #f59e0b; }
        .btn { display: inline-block; margin-top: 12px; padding: 10px 16px; border-radius: 8px; background: var(--brand); color: #fff; text-decoration: none; font-size: 14px; }
        .empty { color: var(--muted); font-size: 14px; }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <a href="{{ route('katalog.beranda') }}">&larr; Kembali ke katalog</a>
        <h1>{{ $destination->name }}</h1>
        <div>📍 {{ $destination->location }}</div>
    </div>
</header>

<main>
    <div class="panel">
        <h2>Deskripsi</h2>
        <p class="meta">{{ $destination->description }}</p>
        @if ($destination->address)
            <p class="meta">Alamat: {{ $destination->address }}</p>
        @endif
        @if ($destination->opening_time && $destination->closing_time)
            <p class="meta">Jam buka: {{ $destination->opening_time }} — {{ $destination->closing_time }}</p>
        @endif
        <a class="btn" href="/">Booking tiket</a>
    </div>

    <div class="panel">
        <h2>Jenis Tiket</h2>
        @if ($destination->ticketTypes->isEmpty())
            <p class="empty">Belum ada jenis tiket untuk destinasi ini.</p>
        @else
            <table>
                <thead>
                    <tr><th>Jenis</th><th>Harga</th><th>Kuota harian</th></tr>
                </thead>
                <tbody>
                    @foreach ($destination->ticketTypes as $type)
                        <tr>
                            <td>{{ $type->name }}</td>
                            <td class="price">Rp {{ number_format((float) $type->price, 0, ',', '.') }}</td>
                            <td>{{ $type->default_quota }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="panel">
        <h2>Ulasan Pengunjung</h2>
        @forelse ($destination->reviews as $review)
            <div class="review">
                <div class="stars">{{ str_repeat('★', max(0, min(5, (int) $review->rating))) }}{{ str_repeat('☆', 5 - max(0, min(5, (int) $review->rating))) }}</div>
                <strong>{{ $review->user?->name ?? 'Pengunjung' }}</strong>
                <p class="meta">{{ $review->comment }}</p>
            </div>
        @empty
            <p class="empty">Belum ada ulasan.</p>
        @endforelse
    </div>
</main>
</body>
</html>
