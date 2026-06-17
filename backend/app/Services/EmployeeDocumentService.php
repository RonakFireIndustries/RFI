<?php

namespace App\Services;

use App\Models\EmployeeDocument;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;

class EmployeeDocumentService
{
    /**
     * Store a new employee document.
     */
    public function storeDocument(int $employeeId, array $data, UploadedFile $file, int $uploadedBy)
    {
        $originalName = $file->getClientOriginalName();
        $mimeType = $file->getMimeType();
        $fileSize = $file->getSize();
        $extension = $file->getClientOriginalExtension();
        
        $generatedName = uniqid('doc_') . '_' . time() . '.' . $extension;
        $path = $file->storeAs('private/employees/' . $employeeId, $generatedName, 'local');

        return EmployeeDocument::create([
            'documentable_id' => $employeeId,
            'file_name' => $generatedName,
            'file_path' => $path,
            'file_type' => $extension,
            'original_file_name' => $originalName,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'document_type' => $data['document_type'],
            'expiry_date' => $data['expiry_date'] ?? null,
            'remarks' => $data['remarks'] ?? null,
            'uploaded_by' => $uploadedBy,
        ]);
    }

    /**
     * Update an existing document metadata or replace file.
     */
    public function updateDocument(EmployeeDocument $document, array $data, ?UploadedFile $file = null)
    {
        $updateData = [];

        if (isset($data['document_type'])) {
            $updateData['document_type'] = $data['document_type'];
        }
        if (array_key_exists('expiry_date', $data)) {
            $updateData['expiry_date'] = $data['expiry_date'];
        }
        if (array_key_exists('remarks', $data)) {
            $updateData['remarks'] = $data['remarks'];
        }

        if ($file) {
            // Delete old file
            if (Storage::disk('local')->exists($document->file_path)) {
                Storage::disk('local')->delete($document->file_path);
            }

            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getMimeType();
            $fileSize = $file->getSize();
            $extension = $file->getClientOriginalExtension();
            
            $generatedName = uniqid('doc_') . '_' . time() . '.' . $extension;
            $path = $file->storeAs('private/employees/' . $document->documentable_id, $generatedName, 'local');

            $updateData['file_name'] = $generatedName;
            $updateData['file_path'] = $path;
            $updateData['file_type'] = $extension;
            $updateData['original_file_name'] = $originalName;
            $updateData['mime_type'] = $mimeType;
            $updateData['file_size'] = $fileSize;
        }

        $document->update($updateData);

        return $document->fresh();
    }

    /**
     * Soft delete document.
     */
    public function deleteDocument(EmployeeDocument $document)
    {
        return $document->delete();
    }

    /**
     * Restore document.
     */
    public function restoreDocument(EmployeeDocument $document)
    {
        return $document->restore();
    }

    /**
     * Get expiring documents.
     */
    public function getExpiringDocuments()
    {
        $now = now();
        $in30Days = now()->addDays(30);
        $in60Days = now()->addDays(60);
        $in90Days = now()->addDays(90);

        $documents = EmployeeDocument::with('employee')
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', $now->toDateString())
            ->where('expiry_date', '<=', $in90Days->toDateString())
            ->orderBy('expiry_date', 'asc')
            ->get();

        $expiring = [
            '30_days' => [],
            '60_days' => [],
            '90_days' => [],
        ];

        foreach ($documents as $doc) {
            $expiry = \Carbon\Carbon::parse($doc->expiry_date);
            if ($expiry->lte($in30Days)) {
                $expiring['30_days'][] = $doc;
            } elseif ($expiry->lte($in60Days)) {
                $expiring['60_days'][] = $doc;
            } else {
                $expiring['90_days'][] = $doc;
            }
        }

        return $expiring;
    }
}
