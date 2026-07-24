<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\SiteVisit;
use App\Models\SiteVisitPhoto;
use App\Models\SiteVisitVoiceNote;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SiteVisitController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = SiteVisit::with(['building', 'user', 'photos', 'voiceNotes']);

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->building_id);
        }
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        if ($request->filled('date_from')) {
            $query->where('visit_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('visit_date', '<=', $request->date_to);
        }

        $perPage = (int) $request->input('per_page', 15);
        $visits = $query->latest('visit_date')->paginate($perPage);

        return $this->success('Site visits retrieved', [
            'site_visits' => $visits->items(),
            'pagination' => [
                'total' => $visits->total(),
                'per_page' => $visits->perPage(),
                'current_page' => $visits->currentPage(),
                'last_page' => $visits->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'building_id' => ['required', 'exists:buildings,id'],
            'visit_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'discussion_notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date', 'after_or_equal:today'],
        ]);

        $validated['user_id'] = $request->user()->id;

        $visit = SiteVisit::create($validated);

        $this->logActivity($visit, 'created', 'Site visit created');

        NotificationService::create(
            $request->user()->id,
            'Site visit scheduled',
            'Visit to ' . $visit->building->name . ' on ' . $visit->visit_date,
            'info',
            '/dashboard/site-visits/' . $visit->id,
        );

        return $this->success('Site visit created', ['site_visit' => $visit->load(['building', 'user'])], [], 201);
    }

    public function show(SiteVisit $siteVisit): JsonResponse
    {
        $this->authorize('building.view');
        $siteVisit->load(['building', 'user', 'photos', 'voiceNotes', 'documents']);
        return $this->success('Site visit retrieved', ['site_visit' => $siteVisit]);
    }

    public function update(Request $request, SiteVisit $siteVisit): JsonResponse
    {
        $this->authorize('building.edit');

        $validated = $request->validate([
            'visit_date' => ['sometimes', 'date'],
            'purpose' => ['nullable', 'string', 'max:255'],
            'discussion_notes' => ['nullable', 'string'],
            'next_followup_date' => ['nullable', 'date'],
        ]);

        $siteVisit->update($validated);

        return $this->success('Site visit updated', ['site_visit' => $siteVisit->fresh()->load(['building', 'user'])]);
    }

    public function destroy(SiteVisit $siteVisit): JsonResponse
    {
        $this->authorize('building.delete');

        foreach ($siteVisit->photos as $photo) {
            Storage::disk('public')->delete($photo->file_path);
        }
        foreach ($siteVisit->voiceNotes as $note) {
            Storage::disk('public')->delete($note->file_path);
        }

        $siteVisit->delete();
        return $this->success('Site visit deleted');
    }

    public function uploadPhotos(Request $request, SiteVisit $siteVisit): JsonResponse
    {
        $this->authorize('building.edit');

        $request->validate([
            'photos' => ['required', 'array', 'max:20'],
            'photos.*' => ['file', 'mimes:jpg,jpeg,png,gif,webp', 'max:10240'],
        ]);

        $uploaded = [];
        foreach ($request->file('photos') as $file) {
            $path = $file->store('site-visit-photos', 'public');
            $photo = $siteVisit->photos()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'uploaded_by' => $request->user()->id,
            ]);
            $uploaded[] = $photo;
        }

        return $this->success('Photos uploaded', ['photos' => $uploaded], [], 201);
    }

    public function deletePhoto(SiteVisit $siteVisit, SiteVisitPhoto $photo): JsonResponse
    {
        $this->authorize('building.edit');

        Storage::disk('public')->delete($photo->file_path);
        $photo->delete();

        return $this->success('Photo deleted');
    }

    public function uploadVoiceNotes(Request $request, SiteVisit $siteVisit): JsonResponse
    {
        $this->authorize('building.edit');

        $request->validate([
            'voice_notes' => ['required', 'array', 'max:10'],
            'voice_notes.*' => ['file', 'mimes:webm,mp3,ogg,wav', 'max:20480'],
            'durations' => ['nullable', 'array'],
            'durations.*' => ['nullable', 'integer'],
        ]);

        $uploaded = [];
        foreach ($request->file('voice_notes') as $index => $file) {
            $path = $file->store('site-visit-voice-notes', 'public');
            $note = $siteVisit->voiceNotes()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                'duration_seconds' => $request->input("durations.{$index}"),
                'uploaded_by' => $request->user()->id,
            ]);
            $uploaded[] = $note;
        }

        return $this->success('Voice notes uploaded', ['voice_notes' => $uploaded], [], 201);
    }

    public function deleteVoiceNote(SiteVisit $siteVisit, SiteVisitVoiceNote $voiceNote): JsonResponse
    {
        $this->authorize('building.edit');

        Storage::disk('public')->delete($voiceNote->file_path);
        $voiceNote->delete();

        return $this->success('Voice note deleted');
    }

    protected function logActivity($model, string $action, string $description): void
    {
        $model->activityLogs()->create([
            'user_id' => request()->user()?->id,
            'loggable_type' => get_class($model),
            'loggable_id' => $model->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}
