@php
    $code = 419;
    $title = 'Sesi Kedaluwarsa';
    $isAdminLogin = request()->is('admin/login');
    $description = $isAdminLogin
        ? 'Sesi login sudah kedaluwarsa. Buka kembali formulir login admin, lalu masukkan email dan password lagi.'
        : 'Halaman terlalu lama dibuka sehingga token keamanan tidak berlaku lagi. Muat ulang halaman lalu coba kembali.';
    $retryUrl = $isAdminLogin ? route('admin.login') : url('/');
    $retryLabel = $isAdminLogin ? 'Buka Ulang Login Admin' : 'Kembali ke Beranda';
@endphp
@include('errors.minimal')
