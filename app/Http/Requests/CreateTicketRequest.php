<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateTicketRequest extends FormRequest
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
            '*.ticket_id' => 'nullable|string|size:14',
            '*.events_ticket_id' => 'required|integer|exists:event_tickets,id',
            '*.ticket_status' => 'nullable|string|exists:ticket_status,description',
            '*.holder' => 'required|array',
            '*.holder.name' => 'required|string',
            '*.holder.category' => 'required|string|exists:holder_categories,description',
            '*.holder.photo' => 'nullable|string',
            '*.holder.organization' => 'nullable|string',
            '*.holder.position' => 'nullable|string',
            '*.holder.dob' => 'nullable|string',
            '*.holder.phone' => 'nullable|string',
            '*.holder.email' => 'nullable|string',
            '*.holder.address' => 'nullable|string',
            '*.holder.city' => 'nullable|string',
        ];
    }
}
