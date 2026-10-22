<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Ticket extends Model
{
    public const STATUS_VALID = 'valid';

    public const STATUS_USED = 'used';

    public const STATUS_VOID = 'void';

    protected $fillable = [
        'booking_id',
        'booking_item_id',
        'ticket_type_id',
        'user_id',
        'visit_date',
        'code',
        'ticket_type_name',
        'unit_price',
        'status',
        'used_at',
        'validated_by',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date:Y-m-d',
            'unit_price' => 'decimal:2',
            'used_at' => 'datetime',
        ];
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function bookingItem(): BelongsTo
    {
        return $this->belongsTo(BookingItem::class);
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }
}
