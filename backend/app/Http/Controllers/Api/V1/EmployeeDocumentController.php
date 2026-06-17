<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\EmployeeDocument;
use App\Services\EmployeeDocumentService;
use App\Http\Resources\EmployeeDocumentResource;
use App\Http\Requests\StoreEmployeeDocumentRequest;
use App\Http\Requests\UpdateEmployeeDocumentRequest;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmployeeDocumentController extends Controller
{
    use ApiResponse;

    protected $documentService;

    public function __construct(EmployeeDocumentService $documentService)
    {
        $this->documentService = $documentService;
    }

    /**
     * List documents for a specific employee.
     */
    public function index($employeeId, Request $request)
    {
        $this->authorize('viewAny', EmployeeDocument::class);

        $documents = EmployeeDocument::with('uploader')
            ->withTrashed()
            ->where('documentable_id', $employeeId)
            ->latest()
            ->paginate($request->get('per_page', 15));

        return $this->success(
            'Employee documents retrieved successfully.',
            EmployeeDocumentResource::collection($documents)->response()->getData(true)
        );
    }

    /**
     * Store a new document for a specific employee.
     */
    public function store(StoreEmployeeDocumentRequest $request, $employeeId)
    {
        $document = $this->documentService->storeDocument(
            $employeeId,
            $request->validated(),
            $request->file('file'),
            $request->user()->id
        );

        return $this->success(
            'Document uploaded successfully.',
            new EmployeeDocumentResource($document->load('uploader')),
            [],
            201
        );
    }

    /**
     * Get a specific document details.
     */
    public function show($id)
    {
        $document = EmployeeDocument::with('uploader')->findOrFail($id);
        
        $this->authorize('view', $document);

        return $this->success(
            'Document details retrieved successfully.',
            new EmployeeDocumentResource($document)
        );
    }

    /**
     * Update document metadata or replace file.
     */
    public function update(UpdateEmployeeDocumentRequest $request, $id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        $document = $this->documentService->updateDocument(
            $document,
            $request->validated(),
            $request->file('file')
        );

        return $this->success(
            'Document updated successfully.',
            new EmployeeDocumentResource($document->load('uploader'))
        );
    }

    /**
     * Delete document (Soft delete).
     */
    public function destroy($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        $this->authorize('delete', $document);

        $this->documentService->deleteDocument($document);

        return $this->success('Document deleted successfully.', null);
    }

    /**
     * Download the file.
     */
    public function download($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        // Use download authorization if needed, falling back to view
        if (request()->user()->cannot('document.download') && request()->user()->cannot('document.view')) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            return $this->error('File not found in storage.', 404);
        }

        return Storage::disk('local')->download($document->file_path, $document->original_file_name);
    }

    /**
     * Preview the file inline.
     */
    public function preview($id)
    {
        $document = EmployeeDocument::findOrFail($id);
        
        if (request()->user()->cannot('document.preview') && request()->user()->cannot('document.view')) {
            abort(403, 'Unauthorized');
        }

        if (!Storage::disk('local')->exists($document->file_path)) {
            return $this->error('File not found in storage.', 404);
        }

        $file = Storage::disk('local')->get($document->file_path);
        $type = Storage::disk('local')->mimeType($document->file_path);

        return response($file, 200)->header('Content-Type', $type);
    }

    /**
     * Get expiring documents grouped by 30, 60, 90 days.
     */
    public function expiring(Request $request)
    {
        if ($request->user()->cannot('document.manage-expiry')) {
            abort(403, 'Unauthorized');
        }

        $expiring = $this->documentService->getExpiringDocuments();

        return $this->success('Expiring documents retrieved successfully.', [
            '30_days' => EmployeeDocumentResource::collection($expiring['30_days']),
            '60_days' => EmployeeDocumentResource::collection($expiring['60_days']),
            '90_days' => EmployeeDocumentResource::collection($expiring['90_days']),
        ]);
    }
}
