<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'destination_id' => ['required', 'integer', 'exists:destinations,id'],
            'visit_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.ticket_type_id' => ['required', 'integer', 'exists:ticket_types,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'voucher_code' => ['nullable', 'string', 'max:50'],
            'customer_name' => ['nullable', 'string', 'max:100'],
            'customer_phone' => ['nullable', 'string', 'max:20'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'destination_id.required' => 'Destinasi wajib dipilih.',
            'destination_id.exists' => 'Destinasi tidak ditemukan.',
            'visit_date.required' => 'Tanggal kunjungan wajib diisi.',
            'visit_date.date_format' => 'Format tanggal harus YYYY-MM-DD.',
            'visit_date.after_or_equal' => 'Tanggal kunjungan tidak boleh di masa lalu.',
            'items.required' => 'Minimal 1 jenis tiket harus dipesan.',
            'items.*.ticket_type_id.exists' => 'Jenis tiket tidak ditemukan.',
            'items.*.quantity.min' => 'Jumlah tiket minimal 1.',
            'items.*.quantity.max' => 'Jumlah tiket maksimal 20 per jenis.',
            'voucher_code.max' => 'Kode voucher terlalu panjang.',
        ];
    }
}
