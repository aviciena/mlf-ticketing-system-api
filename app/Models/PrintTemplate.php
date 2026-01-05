<?php

namespace App\Models;

use App\Traits\Hashidable;

class PrintTemplate extends BaseModel
{
    use Hashidable;

    protected $fillable = [
        'config_name',
        'card_width',
        'card_height',
        'margin_top',
        'margin_bottom',
        'margin_left',
        'margin_right',
        'font_size_name',
        'font_size_organizer',
        'font_size_ticket_number',
        'margin_top_content',
        'margin_bottom_content',
        'margin_left_content',
        'margin_right_content',
        'qr_margin',
        'qr_width',
        'qr_height',
        'use_layout',
        'layout_base64',
        'layout_file_name',
        'layout_width',
        'layout_height',
        'created_by',
        'updated_by',
    ];
}
