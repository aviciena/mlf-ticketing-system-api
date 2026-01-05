<?php

namespace App\Http\Controllers;

use App\Helpers\HashidHelper;
use App\Http\Resources\GateResource;
use App\Models\Gate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GateController extends BaseController
{
    /**
     * Get All Gates
     */
    public function index(Request $request)
    {
        $query = Gate::query();

        if ($request->filled('active')) {
            $query->where('active', $request->active);
        }

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $gates = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            GateResource::collection($gates),
            'Gate list retrieved successfully',
            $meta
        );
    }

    /**
     * Create Gate
     */
    public function create(Request $request)
    {
        $validate = $request->validate([
            'description' => 'nullable|string|max:255|unique:gates,description',
            'active' => 'nullable|boolean'
        ]);

        $validate['code'] = strtolower(str_replace(" ", "", $request->description));

        $ticket = Gate::create($validate);

        return $this->sendResponse(
            new GateResource($ticket),
            'Gate created successfully',
            null,
            201
        );
    }

    /**
     * Update Gate
     */
    public function update(Request $request, $id)
    {
        $gate = Gate::findByHashid($id);
        if (!$gate) {
            return response()->json([
                'success' => false,
                'message' => 'Gate not found',
                'data' => []
            ], 422);
        }

        $request->validate([
            'description' => ['nullable', 'string', 'max:255', Rule::unique('gates', 'description')->ignore(HashidHelper::decode($id))],
            'active' => 'nullable|boolean'
        ]);

        $request['code'] = strtolower(str_replace(" ", "", $request->description));

        $gate->update($request->all());

        return $this->sendResponse(
            new GateResource($gate),
            'Gate updated successfully'
        );
    }

    /**
     * Delete Gate
     */
    public function delete($id)
    {
        $gate = Gate::findByHashid($id);
        if (!$gate) {
            return response()->json([
                'success' => false,
                'message' => 'Gate not found',
                'data' => []
            ], 422);
        }

        $gate->delete();

        return response()->json([
            'success' => true,
            'message' => 'Gate deleted successfully',
            'data' => []
        ]);
    }
}
