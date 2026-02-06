<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function showStorage($path)
    {
        /* Fix storage un comment when failed show storage */
        /*
        $paths = [
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('framework/cache'),
        ];

        foreach ($paths as $path) {
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
        }

        // Hapus file cache bootstrap secara programatik
        $configCache = base_path('bootstrap/cache/config.php');
        if (file_exists($configCache)) {
            unlink($configCache);
        }
        */
        /* End Fix storage */

        // Path lengkap di dalam folder storage/app/public/
        $fullPath = "public/" . $path;

        // Cek apakah file benar-benar ada
        if (!Storage::exists($fullPath)) {
            abort(404);
        }

        // Ambil isi file dan tipe filenya (mime type)
        $file = Storage::get($fullPath);
        $type = Storage::mimeType($fullPath);

        // Kembalikan sebagai response file (gambar/pdf/dll)
        return response($file, 200)->header("Content-Type", $type);
    }
}
