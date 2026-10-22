<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>JelajahKu — Booking Tiket Destinasi</title>
    <style>
        :root { --brand: #0f766e; --brand-dark: #115e59; --ink: #1f2937; --muted: #6b7280; --line: #e5e7eb; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; color: var(--ink); background: #f9fafb; }
        header { background: var(--brand); color: #fff; padding: 20px 24px; }
        header .wrap { max-width: 1080px; margin: 0 auto; display: flex; align-items: center; justify-content: space-between; gap: 16px; }
        header h1 { margin: 0; font-size: 22px; }
        header p { margin: 4px 0 0; opacity: .85; font-size: 13px; }
        main { max-width: 1080px; margin: 0 auto; padding: 24px; }
        .filters { display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 24px; }
        .filters input, .filters select { padding: 10px 12px; border: 1px solid var(--line); border-radius: 8px; font-size: 14px; background: #fff; }
        .filters input { flex: 1; min-width: 200px; }
        .filters button { padding: 10px 20px; border: 0; border-radius: 8px; background: var(--brand-dark); color: #fff; font-size: 14px; cursor: pointer; }
        .grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 20px; }
        .card { background: #fff; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; }
        .card .thumb { height: 150px; background: linear-gradient(135deg, #99f6e4, #5eead4); display: flex; align-items: center; justify-content: center; color: var(--brand-dark); font-weight: 700; }
        .card .body { padding: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
        .card h3 { margin: 0; font-size: 17px; }
        .badge { display: inline-block; font-size: 12px; padding: 2px 8px; border-radius: 999px; background: #ecfeff; color: var(--brand-dark); width: fit-content; }
        .meta { color: var(--muted); font-size: 13px; margin: 0; }
        .price { margin-top: auto; font-weight: 700; color: var(--brand-dark); }
        .empty { text-align: center; padding: 60px 20px; color: var(--muted); }
        .btn { display: inline-block; margin-top: 8px; padding: 8px 14px; border-radius: 8px; background: var(--brand); color: #fff; text-decoration: none; font-size: 14px; }
    </style>
</head>
<body>
<header>
    <div class="wrap">
        <div>
            <h1>JelajahKu</h1>
            <p>Booking tiket destinasi wisata lokal — cepat &amp; tanpa antre.</p>
        </div>
        <div>{{ $destinations->count() }} destinasi</div>
    </div>
</header>

<main>
    <form class="filters" method="get" action="{{ route('katalog.beranda') }}">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari destinasi atau lokasi...">
        <select name="category">
            <option value="">Semua kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
            @endforeach
        </select>
        <button type="submit">Cari</button>
    </form>

    @if ($destinations->isEmpty())
        <div class="empty">Tidak ada destinasi yang cocok dengan pencarianmu.</div>
    @else
        <div class="grid">
            @foreach ($destinations as $destination)
                <article class="card">
                    <div class="thumb">{{ strtoupper(substr($destination->name, 0, 2)) }}</div>
                    <div class="body">
                        @if ($destination->category)
                            <span class="badge">{{ $destination->category->name }}</span>
                        @endif
                        <h3>{{ $destination->name }}</h3>
                        <p class="meta">📍 {{ $destination->location }}</p>
                        <p class="price">
                            @if ($destination->min_price !== null)
                                Mulai Rp {{ number_format((float) $destination->min_price, 0, ',', '.') }}
                            @else
                                Harga belum tersedia
                            @endif
                        </p>
                        <a class="btn" href="{{ route('katalog.detail', $destination->slug) }}">Lihat detail</a>
                    </div>
                </article>
            @endforeach
        </div>
    @endif
</main>
</body>
</html>
