<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVenueRequest extends FormRequest
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
            'title' => 'required|string',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'street' => 'required|string',
            'city' => 'required|string',
            'postal_code' => 'required|integer',
            'province' => 'required|string',
            'latitude' => 'nullable|string',
            'longitude' => 'nullable|string',
            'maps_embed' => 'nullable|string',
            'maps' => 'nullable|string',
        ];
    }
}
