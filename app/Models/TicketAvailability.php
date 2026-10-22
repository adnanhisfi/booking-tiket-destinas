<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketAvailability extends Model
{
    protected $table = 'ticket_availability';

    protected $fillable = [
        'ticket_type_id',
        'visit_date',
        'quota',
        'sold',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date:Y-m-d',
            'quota' => 'integer',
            'sold' => 'integer',
        ];
    }

    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(TicketType::class);
    }

    public function remaining(): int
    {
        return max(0, $this->quota - $this->sold);
    }
}
