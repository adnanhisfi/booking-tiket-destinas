@if (session('ok'))
    <div class="mx-4 mt-4 mb-2 rounded-xl bg-soft text-primary text-sm px-4 py-3">{{ session('ok') }}</div>
@endif
@if ($errors->any())
    <div class="mx-4 mt-4 mb-2 rounded-xl bg-red-50 text-red-600 text-sm px-4 py-3">
        @foreach ($errors->all() as $e)<p>{{ $e }}</p>@endforeach
    </div>
@endif
