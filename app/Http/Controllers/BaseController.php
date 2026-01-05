<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class BaseController extends Controller
{
    protected function validateException($message)
    {
        throw ValidationException::withMessages($message);
    }

    /**
     * Success response
     */
    public function sendResponse($result, $message = null, $meta = null, $code = Response::HTTP_OK)
    {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $result
        ];

        if (isset($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $code);
    }

    /**
     * Error response
     */
    public function sendError($error, $errorMessages = [], $code = Response::HTTP_BAD_REQUEST)
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];

        if (!empty($errorMessages)) {
            $response['errors'] = $errorMessages;
        }

        return response()->json($response, $code);
    }

    /**
     * Get Pagination from request
     * @return JSON Object
     */
    public function getPagination(Request $request, $query)
    {
        // Offset Pagination
        $limit = intval($request->get('pageSize', 10)); // default limit 10
        $start = intval($request->get('page', 1));  // default start 0
        $start = ($start - 1) * $limit;

        $total = $query->count();

        // calculate total pages
        $totalPages = (int) ceil($total / $limit);

        return [
            'meta' => [
                'total' => $total,
                'total_pages' => $totalPages,
                'current_page' => (int) floor($start / $limit) + 1,
                'page_size' => $limit,
            ],
            'start' => $start,
            'limit' => $limit
        ];
    }
}
