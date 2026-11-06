<?php

namespace App\Http\Requests\Web;

use App\Http\Requests\Api\StoreBookingRequest as ApiStoreBookingRequest;
use App\Models\Destination;

class StoreBookingRequest extends ApiStoreBookingRequest
{
    /**
     * Buang jenis tiket yang tidak dipilih (jumlah 0) sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        $destination = $this->route('destination');

        if ($destination instanceof Destination) {
            $this->merge(['destination_id' => $destination->id]);
        }

        $items = collect($this->input('items', []))
            ->filter(fn ($item) => is_array($item) && (int) ($item['quantity'] ?? 0) > 0)
            ->values()
            ->all();

        $this->merge(['items' => $items]);
    }
}
