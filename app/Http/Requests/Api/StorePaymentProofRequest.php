<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentProofRequest extends FormRequest
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
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'proof.required' => 'Bukti transfer wajib diunggah.',
            'proof.file' => 'Bukti transfer harus berupa berkas.',
            'proof.mimes' => 'Format bukti harus JPG, PNG, atau PDF.',
            'proof.max' => 'Ukuran bukti maksimal 4 MB.',
        ];
    }
}
