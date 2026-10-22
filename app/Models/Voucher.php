<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Voucher extends Model
{
    public const TYPE_PERCENT = 'percent';

    public const TYPE_FIXED = 'fixed';

    protected $fillable = [
        'code',
        'type',
        'value',
        'min_amount',
        'max_discount',
        'quota',
        'used',
        'destination_id',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'min_amount' => 'decimal:2',
            'max_discount' => 'decimal:2',
            'quota' => 'integer',
            'used' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Destination::class);
    }

    public function isApplicable(?int $destinationId, float $amount): ?string
    {
        if ($this->status !== 'active') {
            return 'Voucher tidak aktif.';
        }

        $now = now();

        if (($this->start_at && $this->start_at->isAfter($now)) || ($this->end_at && $this->end_at->isBefore($now))) {
            return 'Voucher di luar periode berlaku.';
        }

        if ($this->quota > 0 && $this->used >= $this->quota) {
            return 'Kuota voucher sudah habis.';
        }

        if ($this->destination_id !== null && $this->destination_id !== $destinationId) {
            return 'Voucher tidak berlaku untuk destinasi ini.';
        }

        if ($amount < (float) $this->min_amount) {
            return 'Minimal transaksi untuk voucher ini adalah Rp'.number_format($this->min_amount, 0, ',', '.').'.';
        }

        return null;
    }

    public function discountFor(float $amount): float
    {
        if ($this->type === self::TYPE_PERCENT) {
            $discount = $amount * ((float) $this->value / 100);

            if ($this->max_discount !== null) {
                $discount = min($discount, (float) $this->max_discount);
            }

            return round($discount, 2);
        }

        return min($amount, (float) $this->value);
    }
}
