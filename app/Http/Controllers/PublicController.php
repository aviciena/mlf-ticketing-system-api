<?php

namespace App\Http\Controllers;

use App\Helpers\Utils;
use App\Models\Ticket;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PublicController extends BaseController
{
    public function getQRImage($id)
    {
        try {
            $ticket = Ticket::where('id', $id)->first();

            if (!empty($ticket->id)) {
                // Generate the QR code
                $qrCode = QrCode::size(200)->backgroundColor(255, 255, 255)->generate($ticket->id);

                // Set the response headers
                $headers = ['Content-Type' => 'image/svg+xml'];

                // Return the image response
                return response($qrCode, 200, $headers);
            } else {
                return response()->json(['status' => 404, 'img' => null], 404);
            }
        } catch (\Throwable $th) {
            Log::info($th);
            return response()->json(['status' => 404, 'img' => null], 404);
        }
    }
}
