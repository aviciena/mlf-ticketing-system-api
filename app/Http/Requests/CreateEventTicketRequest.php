<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Menyiapkan data sebelum validasi dijalankan.
     */
    protected function prepareForValidation()
    {
        // Cek apakah input 'description' ada isinya
        if ($this->has('description')) {
            // Bersihkan tag dan spasi kosong
            $clean = trim(str_replace('&nbsp;', ' ', strip_tags($this->description)));

            // Jika setelah dibersihkan ternyata kosong, ubah menjadi null
            if (mb_strlen($clean) === 0) {
                $this->merge([
                    'description' => null,
                ]);
            }
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
            'event_id' => 'required|string|exists:events,id',
            'title' => ['required', 'string', 'max:255', Rule::unique('event_tickets')->where(function ($query) {
                return $query->where('event_id', $this->event_id);
            })],
            'event_ticket_category' => 'required|string|exists:events_ticket_categories,description',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'sale_start_date' => 'nullable|date',
            'sale_end_date' => 'nullable|date|after_or_equal:sale_start_date',
            'min_quantity' => 'nullable|integer|min:1',
            'max_quantity' => 'nullable|integer|min:1',
            'quota' => 'nullable|integer|min:0',
            'price' => 'nullable|integer|min:0',
            'original_price' => 'nullable|integer|min:0',
            'discount_type' => 'nullable|string',
            'discount_amount' => 'nullable|integer|min:0',
            'price_after_discount' => 'nullable|integer|min:0',
            'allow_multiple_checkin' => 'nullable|boolean',
            'validity_type' => 'nullable|string|exists:validity_tickets,description',
            'auto_checkout' => 'nullable|boolean',
            'external_event_ticket_id' => 'nullable|string',
            'description' => 'nullable|string',
        ];
    }
}
