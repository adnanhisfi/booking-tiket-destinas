@php
    $key = strtolower((string) $s);
    $warna = [
        'pending' => 'bg-amber-50 text-amber-600',
        'menunggu' => 'bg-amber-50 text-amber-600',
        'paid' => 'bg-green-50 text-green-700',
        'aktif' => 'bg-green-50 text-green-700',
        'active' => 'bg-green-50 text-green-700',
        'valid' => 'bg-green-50 text-green-700',
        'completed' => 'bg-gray-100 text-gray-500',
        'selesai' => 'bg-gray-100 text-gray-500',
        'used' => 'bg-gray-100 text-gray-500',
        'expired' => 'bg-red-50 text-red-500',
        'cancelled' => 'bg-red-50 text-red-500',
        'failed' => 'bg-red-50 text-red-500',
        'void' => 'bg-red-50 text-red-500',
        'inactive' => 'bg-red-50 text-red-500',
        'nonaktif' => 'bg-red-50 text-red-500',
    ];
    $label = [
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'completed' => 'Selesai',
        'expired' => 'Kedaluwarsa',
        'cancelled' => 'Dibatalkan',
        'valid' => 'Valid',
        'used' => 'Terpakai',
        'void' => 'Void',
        'active' => 'Aktif',
        'inactive' => 'Nonaktif',
        'failed' => 'Gagal',
    ][$key] ?? ucfirst((string) $s);
@endphp
<span class="text-xs px-3 py-1 rounded-full {{ $warna[$key] ?? 'bg-gray-100 text-gray-500' }}">{{ $label }}</span>
