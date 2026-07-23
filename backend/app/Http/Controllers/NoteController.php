<?php

namespace App\Http\Controllers;

use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NoteController extends Controller
{
    public function store(Request $request)
    {
        $this->authorize('manage_inventory');

        $validated = $request->validate([
            'note' => 'required|string',
            'notable_id' => 'required|integer',
            'notable_type' => 'required|string',
        ]);

        $validated['created_by'] = Auth::id();

        $note = Note::create($validated);
        
        return response()->json($note->load('creator'), 201);
    }

    public function destroy($id)
    {
        $this->authorize('manage_inventory');

        $note = Note::findOrFail($id);

        if ($note->created_by !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized to delete this note'], 403);
        }

        $note->delete();
        
        return response()->noContent();
    }
}
