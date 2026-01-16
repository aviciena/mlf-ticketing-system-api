<?php

namespace App\Http\Requests;

use App\Helpers\Utils;
use Illuminate\Foundation\Http\FormRequest;

class FindSubEventsTransactionRequest extends FormRequest
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
        // Cek apakah input 'id' ada isinya
        if ($this->has('id')) {
            $base64 = str_replace(['-', '_'], ['+', '/'], $this->id);
            $encodedId = Utils::decode($base64);

            // Jika setelah di encoded set ulang id
            $this->merge([
                'id' => $encodedId,
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
            'id' => 'required|string|exists:events,id',
            'transaction_id' => 'required|string|exists:transactions,id'
        ];
    }
}
