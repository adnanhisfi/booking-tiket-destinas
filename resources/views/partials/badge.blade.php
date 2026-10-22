@php $warna = ['menunggu' => 'bg-amber-50 text-amber-600', 'aktif' => 'bg-green-50 text-green-700', 'selesai' => 'bg-gray-100 text-gray-500', 'nonaktif' => 'bg-red-50 text-red-500']; @endphp
<span class="text-xs px-3 py-1 rounded-full {{ $warna[strtolower($s)] ?? 'bg-gray-100' }}">{{ ucfirst($s) }}</span>
