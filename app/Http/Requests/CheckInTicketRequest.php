<?php

namespace App\Http\Requests;

use App\Rules\ValidHashid;
use Illuminate\Foundation\Http\FormRequest;

class CheckInTicketRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'ticket_id' => 'required|string|exists:tickets,id',
            'checkin_gate_id' => ['required', 'string', new ValidHashid()]
        ];
    }
}
