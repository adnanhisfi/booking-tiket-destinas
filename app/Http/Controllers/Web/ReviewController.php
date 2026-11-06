<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    /**
     * Simpan ulasan pengunjung untuk destinasi yang sudah dibayar.
     */
    public function store(Request $request, Destination $destination): RedirectResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Pilih rating bintang terlebih dahulu.',
            'rating.min' => 'Rating minimal 1 bintang.',
            'rating.max' => 'Rating maksimal 5 bintang.',
            'comment.max' => 'Ulasan maksimal 1000 karakter.',
        ]);

        $booking = Booking::query()
            ->where('user_id', $request->user()->id)
            ->where('destination_id', $destination->id)
            ->whereIn('status', [Booking::STATUS_PAID, Booking::STATUS_COMPLETED])
            ->latest('id')
            ->first();

        if (! $booking) {
            throw ValidationException::withMessages([
                'review' => 'Ulasan hanya bisa diberikan setelah pembayaran booking di destinasi ini lunas.',
            ]);
        }

        $sudahMengulas = Review::query()
            ->where('user_id', $request->user()->id)
            ->where('destination_id', $destination->id)
            ->exists();

        if ($sudahMengulas) {
            throw ValidationException::withMessages([
                'rating' => 'Anda sudah memberi ulasan untuk destinasi ini.',
            ]);
        }

        Review::create([
            'destination_id' => $destination->id,
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return redirect()
            ->route('katalog.detail', $destination->slug)
            ->with('ok', 'Ulasan berhasil dikirim. Terima kasih!');
    }
}
