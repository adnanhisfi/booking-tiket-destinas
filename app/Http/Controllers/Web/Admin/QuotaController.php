<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\TicketAvailability;
use App\Models\TicketType;
use App\Services\QuotaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class QuotaController extends Controller
{
    public function __construct(protected QuotaService $quota) {}

    public function index(Request $request): View
    {
        $ticketTypes = TicketType::query()
            ->with('destination:id,name')
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->integer('destination_id')))
            ->orderBy('name')
            ->get();

        $rows = TicketAvailability::query()
            ->with('ticketType:id,name,destination_id,default_quota', 'ticketType.destination:id,name')
            ->when($request->filled('ticket_type_id'), fn ($q) => $q->where('ticket_type_id', $request->integer('ticket_type_id')))
            ->when($request->filled('destination_id'), fn ($q) => $q->whereHas(
                'ticketType',
                fn ($t) => $t->where('destination_id', $request->integer('destination_id'))
            ))
            ->when($request->filled('from'), fn ($q) => $q->whereDate('visit_date', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($q) => $q->whereDate('visit_date', '<=', $request->input('to')))
            ->orderByDesc('visit_date')
            ->orderBy('ticket_type_id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.kuota.index', [
            'rows' => $rows,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'ticketTypes' => $ticketTypes,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'visit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'quota' => ['required', 'integer', 'min:0', 'max:100000'],
        ], [
            'ticket_type_id.required' => 'Jenis tiket wajib dipilih.',
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lalu.',
            'quota.required' => 'Kuota wajib diisi.',
            'quota.min' => 'Kuota tidak boleh negatif.',
        ]);

        $ticketType = TicketType::findOrFail($data['ticket_type_id']);

        DB::transaction(function () use ($data, $ticketType) {
            $row = $this->quota->rowFor($ticketType, $data['visit_date'], true);

            if ((int) $data['quota'] < $row->sold) {
                throw ValidationException::withMessages([
                    'quota' => "Kuota tidak boleh lebih kecil dari jumlah tiket terjual ({$row->sold}).",
                ]);
            }

            $row->update(['quota' => (int) $data['quota']]);
        });

        return redirect()
            ->route('admin.quotas.index', [
                'ticket_type_id' => $ticketType->id,
                'from' => $data['visit_date'],
                'to' => $data['visit_date'],
            ])
            ->with('ok', 'Kuota tiket berhasil diperbarui.');
    }
}
