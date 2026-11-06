<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Destination;
use App\Services\ReportService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected ReportService $reports) {}

    public function index(): View
    {
        $summary = $this->reports->dashboard();

        $recent = Booking::query()
            ->with(['user:id,name', 'destination:id,name'])
            ->latest('id')
            ->limit(6)
            ->get();

        $chartSales = $this->reports->sales([
            'from' => now()->subDays(6)->toDateString(),
            'to' => now()->toDateString(),
        ]);

        $daily = collect($chartSales['daily'])->keyBy('date');

        $grafik = collect(range(6, 0))
            ->map(function (int $daysAgo) use ($daily) {
                $date = now()->subDays($daysAgo);

                return [
                    'label' => $date->translatedFormat('D'),
                    'total' => (float) ($daily[$date->toDateString()]['revenue'] ?? 0),
                ];
            })
            ->all();

        $popular = collect($this->reports->sales([
            'from' => now()->subDays(29)->toDateString(),
            'to' => now()->toDateString(),
        ])['by_destination'])->take(4)->values();

        return view('admin.dashboard', [
            'summary' => $summary,
            'recent' => $recent,
            'grafik' => $grafik,
            'popular' => $popular,
            'totalDestinations' => Destination::query()->count(),
        ]);
    }
}
