@php $max = max(1, collect($grafik)->max('total')); @endphp
<div class="flex items-end gap-2 h-40">
    @foreach ($grafik as $g)
        <div class="flex-1 flex flex-col items-center justify-end h-full" title="Rp {{ number_format($g['total'], 0, ',', '.') }}">
            <div class="w-full rounded-t-md bg-primary/80" style="height: {{ max(2, $g['total'] / $max * 100) }}%"></div>
            <span class="text-[10px] text-gray-400 mt-1">{{ $g['label'] }}</span>
        </div>
    @endforeach
</div>
