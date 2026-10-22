<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\Payment;
use App\Models\Ticket;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportService
{
    /**
     * Ringkasan dashboard admin.
     *
     * @return array<string, mixed>
     */
    public function dashboard(): array
    {
        $today = now()->toDateString();

        return [
            'today' => [
                'bookings' => Booking::query()->whereDate('created_at', $today)->count(),
                'revenue' => (float) Payment::query()
                    ->where('status', Payment::STATUS_PAID)
                    ->whereDate('paid_at', $today)
                    ->sum('amount'),
                'tickets_sold' => Ticket::query()->whereDate('created_at', $today)->count(),
                'tickets_used' => Ticket::query()->whereDate('used_at', $today)->count(),
            ],
            'totals' => [
                'bookings' => Booking::query()->count(),
                'revenue' => (float) Payment::query()->where('status', Payment::STATUS_PAID)->sum('amount'),
                'tickets_sold' => Ticket::query()->count(),
                'tickets_used' => Ticket::query()->where('status', Ticket::STATUS_USED)->count(),
            ],
            'pending_payments' => Booking::query()->where('status', Booking::STATUS_PENDING)->count(),
            'awaiting_verification' => Payment::query()
                ->where('status', Payment::STATUS_PENDING)
                ->whereNotNull('proof_path')
                ->count(),
        ];
    }

    /**
     * Laporan penjualan dengan filter rentang tanggal & destinasi.
     *
     * @param  array{from?:string|null, to?:string|null, destination_id?:int|null}  $filters
     * @return array{summary: array<string, mixed>, daily: array<int, array<string, mixed>>, by_destination: array<int, array<string, mixed>>}
     */
    public function sales(array $filters): array
    {
        $from = Carbon::parse($filters['from'] ?? now()->subDays(29)->toDateString())->startOfDay();
        $to = Carbon::parse($filters['to'] ?? now()->toDateString())->endOfDay();
        $destinationId = $filters['destination_id'] ?? null;

        $paidBookings = Booking::query()
            ->whereIn('status', [Booking::STATUS_PAID, Booking::STATUS_COMPLETED])
            ->whereBetween('paid_at', [$from, $to])
            ->when($destinationId, fn ($q) => $q->where('destination_id', $destinationId));

        $summary = [
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'orders' => (clone $paidBookings)->count(),
            'revenue' => (float) (clone $paidBookings)->sum('total_amount'),
            'discount' => (float) (clone $paidBookings)->sum('discount_amount'),
        ];

        $daily = (clone $paidBookings)
            ->select(
                DB::raw('DATE(paid_at) as date'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('SUM(total_amount) as revenue')
            )
            ->groupBy(DB::raw('DATE(paid_at)'))
            ->orderBy('date')
            ->get()
            ->map(fn ($row) => [
                'date' => $row->date,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ])
            ->all();

        $byDestination = Booking::query()
            ->whereIn('bookings.status', [Booking::STATUS_PAID, Booking::STATUS_COMPLETED])
            ->whereBetween('bookings.paid_at', [$from, $to])
            ->when($destinationId, fn ($q) => $q->where('bookings.destination_id', $destinationId))
            ->join('destinations', 'destinations.id', '=', 'bookings.destination_id')
            ->select(
                'destinations.id',
                'destinations.name',
                DB::raw('COUNT(bookings.id) as orders'),
                DB::raw('SUM(bookings.total_amount) as revenue')
            )
            ->groupBy('destinations.id', 'destinations.name')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'destination_id' => (int) $row->id,
                'destination' => $row->name,
                'orders' => (int) $row->orders,
                'revenue' => (float) $row->revenue,
            ])
            ->all();

        return [
            'summary' => $summary,
            'daily' => $daily,
            'by_destination' => $byDestination,
        ];
    }

    /**
     * Data ekspor CSV daftar booking.
     *
     * @param  array<string, mixed>  $filters
     * @return array<int, array<int, string|int|float|null>>
     */
    public function bookingRows(array $filters): array
    {
        $bookings = Booking::query()
            ->with(['user:id,name,email', 'destination:id,name'])
            ->when($filters['from'] ?? null, fn ($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn ($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->when($filters['destination_id'] ?? null, fn ($q, $id) => $q->where('destination_id', $id))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->orderByDesc('id')
            ->get();

        $rows = [[
            'Kode', 'Pengunjung', 'Email', 'Destinasi', 'Tanggal Kunjungan',
            'Subtotal', 'Diskon', 'Total', 'Status', 'Dibuat',
        ]];

        foreach ($bookings as $booking) {
            $rows[] = [
                $booking->code,
                $booking->customer_name,
                $booking->user?->email,
                $booking->destination?->name,
                $booking->visit_date->toDateString(),
                (string) $booking->subtotal,
                (string) $booking->discount_amount,
                (string) $booking->total_amount,
                $booking->status,
                $booking->created_at?->toDateTimeString(),
            ];
        }

        return $rows;
    }
}
