<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(Request $request): View
    {
        $period = (int) $request->input('periode', 30);

        if (! in_array($period, [7, 30, 365], true)) {
            $period = 30;
        }

        $from = $this->parseDate($request->input('from'))?->startOfDay()
            ?? now()->subDays($period - 1)->startOfDay();

        $to = $this->parseDate($request->input('to'))?->endOfDay()
            ?? now()->endOfDay();

        if ($from->gt($to)) {
            [$from, $to] = [$to->copy()->startOfDay(), $from->copy()->endOfDay()];
        }

        $destinationId = $request->integer('destination_id') ?: null;

        $sales = $this->reports->sales([
            'from' => $from->toDateString(),
            'to' => $to->toDateString(),
            'destination_id' => $destinationId,
        ]);

        $summary = $sales['summary'];
        $average = $summary['orders'] > 0
            ? $summary['revenue'] / $summary['orders']
            : 0;

        return view('admin.laporan', [
            'sales' => $sales,
            'summary' => $summary,
            'average' => $average,
            'grafik' => $this->chart($sales['daily'], $from, $to, $period),
            'period' => $period,
            'from' => $from,
            'to' => $to,
            'destinationId' => $destinationId,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $rows = $this->reports->bookingRows($request->only(['from', 'to', 'destination_id', 'status']));

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            foreach ($rows as $row) {
                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'laporan-penjualan-'.now()->format('Ymd-His').'.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * Parse filter tanggal secara aman; nilai tidak valid diabaikan.
     */
    protected function parseDate(mixed $value): ?Carbon
    {
        if (! is_string($value) || trim($value) === '') {
            return null;
        }

        try {
            $date = Carbon::createFromFormat('Y-m-d', $value);

            return $date instanceof Carbon ? $date : null;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $daily
     * @return array<int, array{label: string, total: float}>
     */
    protected function chart(array $daily, Carbon $from, Carbon $to, int $period): array
    {
        if ($period > 62) {
            $months = [];

            $cursor = $from->copy()->startOfMonth();

            while ($cursor->lte($to)) {
                $months[$cursor->format('Y-m')] = [
                    'label' => $cursor->translatedFormat('M'),
                    'total' => 0.0,
                ];
                $cursor->addMonth();
            }

            foreach ($daily as $row) {
                $key = Carbon::parse($row['date'])->format('Y-m');

                if (isset($months[$key])) {
                    $months[$key]['total'] += (float) $row['revenue'];
                }
            }

            return array_values($months);
        }

        $map = collect($daily)->keyBy('date');
        $out = [];

        $cursor = $from->copy();

        while ($cursor->lte($to)) {
            $key = $cursor->toDateString();

            $out[] = [
                'label' => $cursor->format('d/m'),
                'total' => (float) ($map[$key]['revenue'] ?? 0),
            ];

            $cursor->addDay();
        }

        return $out;
    }
}
