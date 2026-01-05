<?php

namespace App\Http\Requests;

use App\Helpers\HashidHelper;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PrintTemplateRequest extends FormRequest
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
        $decodedId = null;

        if (!empty($this->id)) {
            $decodedId = HashidHelper::decode($this->id);
        }

        return [
            'config_name' => ['required', 'string', Rule::unique('print_templates', 'config_name')->ignore($decodedId)],
            'card_width' => 'nullable|numeric',
            'card_height' => 'nullable|numeric',
            'margin_top' => 'nullable|numeric',
            'margin_bottom' => 'nullable|numeric',
            'margin_left' => 'nullable|numeric',
            'margin_right' => 'nullable|numeric',
            'font_size_name' => 'nullable|numeric',
            'font_size_organizer' => 'nullable|numeric',
            'font_size_ticket_number' => 'nullable|numeric',
            'margin_top_content' => 'nullable|numeric',
            'margin_bottom_content' => 'nullable|numeric',
            'margin_left_content' => 'nullable|numeric',
            'margin_right_content' => 'nullable|numeric',
            'qr_margin' => 'nullable|numeric',
            'qr_width' => 'nullable|numeric',
            'qr_height' => 'nullable|numeric',
            'use_layout' => 'nullable|boolean',
            'layout_base64' => 'nullable|string',
            'layout_file_name' => 'nullable|string',
            'layout_width' => 'nullable|numeric',
            'layout_height' => 'nullable|numeric',
            'created_by' => 'nullable|exists:users,id',
            'updated_by' => 'nullable|exists:users,id',
        ];
    }
}
