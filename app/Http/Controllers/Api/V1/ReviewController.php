<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ReviewController extends Controller
{
    public function store(Request $request, Destination $destination): JsonResponse
    {
        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['nullable', 'string', 'max:1000'],
            'booking_code' => ['nullable', 'string'],
        ]);

        $booking = Booking::query()
            ->where('user_id', $request->user()->id)
            ->where('destination_id', $destination->id)
            ->whereIn('status', [Booking::STATUS_PAID, Booking::STATUS_COMPLETED])
            ->when($data['booking_code'] ?? null, fn ($q, $code) => $q->where('code', $code))
            ->latest('id')
            ->first();

        if (! $booking) {
            throw ValidationException::withMessages([
                'booking' => 'Ulasan hanya bisa diberikan setelah Anda menyelesaikan pembayaran di destinasi ini.',
            ]);
        }

        if (Review::where('user_id', $request->user()->id)->where('destination_id', $destination->id)->exists()) {
            throw ValidationException::withMessages([
                'rating' => 'Anda sudah memberi ulasan untuk destinasi ini.',
            ]);
        }

        $review = Review::create([
            'destination_id' => $destination->id,
            'user_id' => $request->user()->id,
            'booking_id' => $booking->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
        ]);

        return response()->json([
            'message' => 'Ulasan tersimpan. Terima kasih!',
            'data' => [
                'id' => $review->id,
                'rating' => $review->rating,
                'comment' => $review->comment,
            ],
        ], 201);
    }
}
