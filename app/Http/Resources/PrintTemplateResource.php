<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PrintTemplateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->hashid,
            'name' => $this->config_name,
            'config_name' => $this->config_name,
            'card_width' => $this->card_width ?? '0',
            'card_height' => $this->card_height ?? '0',
            'margin_top' => $this->margin_top ?? '0',
            'margin_bottom' => $this->margin_bottom ?? '0',
            'margin_left' => $this->margin_left ?? '0',
            'margin_right' => $this->margin_right ?? '0',
            'font_size_name' => $this->font_size_name ?? '12',
            'font_size_organizer' => $this->font_size_organizer ?? '12',
            'font_size_ticket_number' => $this->font_size_ticket_number ?? '12',
            'margin_top_content' => $this->margin_top_content ?? '0',
            'margin_bottom_content' => $this->margin_bottom_content ?? '0',
            'margin_left_content' => $this->margin_left_content ?? '0',
            'margin_right_content' => $this->margin_right_content ?? '0',
            'qr_margin' => $this->qr_margin ?? '0',
            'qr_width' => $this->qr_width ?? '0',
            'use_layout' => $this->use_layout ? "yes" : "no",
            'use_layout_bol' => $this->use_layout == 1,
            'use_layout_str' => $this->use_layout ? "Yes" : "No",
            'layout_base64' => $this->layout_base64,
            'layout_file_name' => $this->layout_file_name,
            'layout_width' => $this->layout_width ?? '0',
            'layout_height' => $this->layout_height ?? '0',
            'is_active' => false,
            'status' => null
        ];
    }
}
