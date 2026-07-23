<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('document.create');

        $request->validate([
            'file' => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx|max:10240', // max 10MB
            'documentable_id' => 'required|integer',
            'documentable_type' => 'required|string',
        ]);

        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        $filePath = $file->storeAs('documents', $fileName, 'public');

        $document = Document::create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $filePath,
            'file_type' => $file->getClientOriginalExtension(),
            'documentable_id' => $request->documentable_id,
            'documentable_type' => $request->documentable_type,
            'uploaded_by' => Auth::id(),
        ]);

        return response()->json($document->load('uploader'), 201);
    }

    public function download($id)
    {
        $this->authorize('document.download');

        $document = Document::findOrFail($id);
        
        if (Storage::disk('public')->exists($document->file_path)) {
            return Storage::disk('public')->download($document->file_path, $document->file_name);
        }

        return response()->json(['error' => 'File not found'], 404);
    }

    public function destroy($id)
    {
        $this->authorize('document.delete');

        $document = Document::findOrFail($id);

        if ($document->uploaded_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to delete this document'], 403);
        }
        
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }
        
        $document->delete();
        
        return response()->noContent();
    }
}
