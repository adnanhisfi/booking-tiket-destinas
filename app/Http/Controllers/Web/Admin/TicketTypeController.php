<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingItem;
use App\Models\Destination;
use App\Models\Ticket;
use App\Models\TicketType;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TicketTypeController extends Controller
{
    public function index(Request $request): View
    {
        $ticketTypes = TicketType::query()
            ->with('destination:id,name')
            ->when($request->filled('destination_id'), fn ($q) => $q->where('destination_id', $request->integer('destination_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')->toString()))
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.tiket.index', [
            'ticketTypes' => $ticketTypes,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request): View
    {
        return view('admin.tiket.form', [
            'ticketType' => null,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'selectedDestination' => $request->integer('destination_id') ?: null,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        TicketType::create($this->validated($request));

        return redirect()
            ->route('admin.tickets.index')
            ->with('ok', 'Jenis tiket berhasil ditambahkan.');
    }

    public function edit(TicketType $ticketType): View
    {
        return view('admin.tiket.form', [
            'ticketType' => $ticketType,
            'destinations' => Destination::query()->orderBy('name')->get(['id', 'name']),
            'selectedDestination' => null,
        ]);
    }

    public function update(Request $request, TicketType $ticketType): RedirectResponse
    {
        $ticketType->update($this->validated($request));

        return redirect()
            ->route('admin.tickets.index')
            ->with('ok', 'Jenis tiket berhasil diperbarui.');
    }

    public function destroy(TicketType $ticketType): RedirectResponse
    {
        $sudahDipakai = BookingItem::query()->where('ticket_type_id', $ticketType->id)->exists()
            || Ticket::query()->where('ticket_type_id', $ticketType->id)->exists();

        if ($sudahDipakai) {
            $ticketType->update(['status' => TicketType::STATUS_INACTIVE]);

            return redirect()
                ->route('admin.tickets.index')
                ->with('ok', 'Jenis tiket dinonaktifkan karena sudah dipakai pada booking.');
        }

        $ticketType->delete();

        return redirect()
            ->route('admin.tickets.index')
            ->with('ok', 'Jenis tiket berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999999'],
            'default_quota' => ['required', 'integer', 'min:0', 'max:100000'],
            'status' => ['required', 'in:active,inactive'],
        ], [
            'destination_id.required' => 'Destinasi wajib dipilih.',
            'destination_id.exists' => 'Destinasi tidak valid.',
            'name.required' => 'Nama jenis tiket wajib diisi.',
            'price.required' => 'Harga tiket wajib diisi.',
            'price.numeric' => 'Harga tiket harus berupa angka.',
            'default_quota.required' => 'Kuota harian wajib diisi.',
            'default_quota.integer' => 'Kuota harian harus berupa angka bulat.',
            'status.required' => 'Status wajib dipilih.',
        ]);
    }
}
