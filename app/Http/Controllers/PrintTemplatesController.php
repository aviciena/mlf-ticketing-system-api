<?php

namespace App\Http\Controllers;

use App\Helpers\HashidHelper;
use App\Http\Requests\PrintTemplateRequest;
use App\Http\Resources\PrintTemplateResource;
use App\Models\PrintTemplate;
use Illuminate\Http\Request;

class PrintTemplatesController extends BaseController
{
    /**
     * Get All Print Setup
     */
    public function index(Request $request)
    {
        $query = PrintTemplate::query();

        // Offset Pagination
        $pagination = $this->getPagination($request, $query);
        $limit = $pagination['limit'];
        $start = $pagination['start'];
        $meta = $pagination['meta'];

        // Pagination Query
        $printTemplates = $query->skip($start)->take($limit)->get();

        return $this->sendResponse(
            PrintTemplateResource::collection($printTemplates),
            'Print Template list retrieved successfully',
            $meta
        );
    }

    public function create(PrintTemplateRequest $request)
    {
        $validated = $request->validated();

        $template = PrintTemplate::create($validated);
        return $this->sendResponse(
            new PrintTemplateResource($template),
            'Template created successfully',
            null,
            201
        );
    }

    public function find($id)
    {
        $template = PrintTemplate::findByHashid($id);
        if (!$template) {
            return $this->sendError('Template not found', [], 422);
        }

        return $this->sendResponse(
            new PrintTemplateResource($template),
            'Print Template retrieved successfully'
        );
    }

    public function update(PrintTemplateRequest $request, $id)
    {
        $template = PrintTemplate::findByHashid($id);
        if (!$template) {
            return $this->sendError('Template not found', [], 422);
        }
        $template->update($request->validated());
        return $this->sendResponse(
            new PrintTemplateResource($template),
            'Template updated successfully'
        );
    }

    public function delete($id)
    {
        $template = PrintTemplate::findByHashid($id);
        if (!$template) {
            return $this->sendError('Template not found', [], 422);
        }
        $template->delete();
        return $this->sendResponse(
            [],
            'Template deleted successfully'
        );
    }
}
