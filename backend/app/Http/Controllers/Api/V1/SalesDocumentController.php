<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SalesDocument;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SalesDocumentController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = SalesDocument::with(['building', 'uploader']);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        if ($request->filled('site_visit_id')) {
            $query->where('site_visit_id', $request->site_visit_id);
        }
        if ($request->filled('opportunity_id')) {
            $query->where('opportunity_id', $request->opportunity_id);
        }
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        $perPage = (int) $request->input('per_page', 15);
        $documents = $query->latest()->paginate($perPage);

        return $this->success('Documents retrieved', [
            'documents' => $documents->items(),
            'pagination' => [
                'total' => $documents->total(),
                'per_page' => $documents->perPage(),
                'current_page' => $documents->currentPage(),
                'last_page' => $documents->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'file' => ['required', 'file', 'max:20480'],
            'name' => ['nullable', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:255'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'site_visit_id' => ['nullable', 'exists:site_visits,id'],
            'opportunity_id' => ['nullable', 'exists:opportunities,id'],
            'expiry_date' => ['nullable', 'date', 'after:today'],
            'parent_document_id' => ['nullable', 'exists:sales_documents,id'],
        ]);

        $file = $request->file('file');
        $path = $file->store('sales-documents', 'public');

        $validated['file_name'] = $file->getClientOriginalName();
        $validated['file_path'] = $path;
        $validated['file_type'] = $file->getMimeType();
        $validated['file_size'] = $file->getSize();
        $validated['uploaded_by'] = $request->user()->id;
        $validated['name'] = $validated['name'] ?? $file->getClientOriginalName();

        if ($validated['parent_document_id']) {
            $parent = SalesDocument::findOrFail($validated['parent_document_id']);
            $validated['version_number'] = $parent->version_number + 1;
        }

        unset($validated['file']);

        $document = SalesDocument::create($validated);

        return $this->success('Document uploaded', ['document' => $document->load(['building', 'uploader'])], [], 201);
    }

    public function show(SalesDocument $salesDocument): JsonResponse
    {
        $this->authorize('building.view');
        $salesDocument->load(['building', 'siteVisit', 'opportunity', 'uploader', 'versions', 'parentDocument']);
        return $this->success('Document retrieved', ['document' => $salesDocument]);
    }

    public function download(SalesDocument $salesDocument): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('building.view');

        return Storage::disk('public')->download(
            $salesDocument->file_path,
            $salesDocument->file_name
        );
    }

    public function destroy(SalesDocument $salesDocument): JsonResponse
    {
        $this->authorize('building.delete');

        Storage::disk('public')->delete($salesDocument->file_path);
        $salesDocument->delete();

        return $this->success('Document deleted');
    }
}
