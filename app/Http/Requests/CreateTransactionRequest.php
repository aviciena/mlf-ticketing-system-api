<?php

namespace App\Http\Requests;

use App\Helpers\Utils;
use Illuminate\Foundation\Http\FormRequest;

class CreateTransactionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // 1. Ambil id yang terenkode
        $encodedId = $this->input('event.id');

        // 2. Lakukan proses decode (Contoh menggunakan fungsi fiktif decodeId)
        // Pastikan Anda menangani kasus jika decode gagal (null/kosong)
        $decodedId = Utils::decode($encodedId);

        // 3. Timpa nilai input 'event.id' dengan nilai yang sudah didecode
        // agar divalidasi oleh 'exists' dengan benar
        if ($decodedId) {
            $this->merge([
                'event' => array_merge($this->event ?? [], ['id' => $decodedId])
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'gender' => 'required|string',
            'name' => 'required|string',
            'email' => 'required|string',
            'phone' => 'required|string',
            'event.id' => 'required|string|exists:events,id',
            'amount' => 'required|integer',
            'total' => 'required|integer',

            'tickets' => 'required|array|min:1',
            'tickets.*.id' => 'required|exists:event_tickets,id', // Cek ID di tabel event_tickets
            'tickets.*.name' => 'required|string',
            'tickets.*.price' => 'required|numeric',
            'tickets.*.quantity' => 'required|integer|min:1',

            'donation_amount' => 'nullable|integer',
            'fee' => 'required|integer',
        ];
    }
}
