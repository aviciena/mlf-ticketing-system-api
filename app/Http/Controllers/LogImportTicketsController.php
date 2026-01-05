<?php

namespace App\Http\Controllers;

use App\Http\Resources\LogImportResource;
use App\Models\LogImportTickets;
use Illuminate\Http\Request;

class LogImportTicketsController extends BaseController
{
    public function index(Request $request)
    {
        $activeEventId = $request->user()->event_id;
        $query = LogImportTickets::where('active_event_id', $activeEventId)->orderBy('created_at', 'desc');

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $logImportTickets = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            LogImportResource::collection($logImportTickets),
            'Log import ticket list retrieved successfully',
            $meta
        );
    }
}
