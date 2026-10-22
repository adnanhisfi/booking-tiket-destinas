<?php

namespace App\Services;

use App\Models\TicketAvailability;
use App\Models\TicketType;
use Carbon\CarbonInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class QuotaService
{
    /**
     * Ambil (atau buat) baris kuota untuk kombinasi jenis tiket + tanggal kunjungan.
     */
    public function rowFor(TicketType $ticketType, CarbonInterface|string $visitDate, bool $lock = false): TicketAvailability
    {
        $date = $visitDate instanceof CarbonInterface
            ? $visitDate->toDateString()
            : date('Y-m-d', strtotime($visitDate));

        $query = TicketAvailability::query()
            ->where('ticket_type_id', $ticketType->id)
            ->whereDate('visit_date', $date);

        if ($lock) {
            $query->lockForUpdate();
        }

        if ($row = $query->first()) {
            return $row;
        }

        try {
            return TicketAvailability::create([
                'ticket_type_id' => $ticketType->id,
                'visit_date' => $date,
                'quota' => $ticketType->default_quota,
                'sold' => 0,
            ]);
        } catch (QueryException) {
            return TicketAvailability::query()
                ->where('ticket_type_id', $ticketType->id)
                ->whereDate('visit_date', $date)
                ->lockForUpdate()
                ->firstOrFail();
        }
    }

    /**
     * Reservasi kuota secara atomik agar tidak terjadi overselling.
     *
     * @throws ValidationException
     */
    public function reserve(TicketType $ticketType, CarbonInterface|string $visitDate, int $quantity): TicketAvailability
    {
        $row = $this->rowFor($ticketType, $visitDate, true);

        $affected = DB::update(
            'UPDATE ticket_availability SET sold = sold + ?, updated_at = ? '
            .'WHERE id = ? AND quota - sold >= ?',
            [$quantity, now(), $row->id, $quantity]
        );

        if ($affected === 0) {
            throw ValidationException::withMessages([
                'items' => "Kuota tiket \"{$ticketType->name}\" untuk tanggal tersebut tidak mencukupi.",
            ]);
        }

        return $row->refresh();
    }

    /**
     * Kembalikan kuota (pembatalan / kedaluwarsa).
     */
    public function release(int $ticketTypeId, CarbonInterface|string $visitDate, int $quantity): void
    {
        $date = $visitDate instanceof CarbonInterface
            ? $visitDate->toDateString()
            : date('Y-m-d', strtotime($visitDate));

        DB::update(
            'UPDATE ticket_availability SET sold = CASE WHEN sold >= ? THEN sold - ? ELSE 0 END, updated_at = ? '
            .'WHERE ticket_type_id = ? AND visit_date = ?',
            [$quantity, $quantity, now(), $ticketTypeId, $date]
        );
    }
}
