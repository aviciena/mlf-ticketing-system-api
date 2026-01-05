<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateEventRequest extends FormRequest
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
            'venue_id' => 'required|string|exists:venues,id',
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('events')->where(function ($query) {
                    return $query->where('venue_id', $this->venue_id); // 2. Gunakan $this langsung
                })->ignore($this->event) // 3. Penting: Abaikan ID sendiri saat Update
            ],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'icon' => 'nullable|string',
            'auto_sync' => 'nullable|boolean',
            'is_sync_interval' => 'nullable|boolean',
            'sync_query' => 'nullable|string',
            'event_external_id' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'main_event_id' => 'nullable|string|exists:events,id',
        ];
    }
}
