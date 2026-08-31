<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Document;
use App\Models\FireSystem;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class FireExtinguisherController extends Controller
{
    use ApiResponse;

    /**
     * List all fire extinguishers across buildings (optionally filtered).
     */
    public function index(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.view');

        $query = FireSystem::extinguishers()->with('building:id,name');

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->integer('building_id'));
        }

        if ($request->filled('due')) {
            // only extinguishers whose next refill is due on/before today
            $query->whereNotNull('next_refill_date')
                  ->whereDate('next_refill_date', '<=', now()->toDateString());
        }

        $extinguishers = $query->orderBy('id', 'desc')->paginate($request->integer('per_page', 50));

        return $this->success('Extinguishers retrieved', [
            'extinguishers' => $extinguishers->items(),
            'pagination' => [
                'total' => $extinguishers->total(),
                'per_page' => $extinguishers->perPage(),
                'current_page' => $extinguishers->currentPage(),
                'last_page' => $extinguishers->lastPage(),
            ],
        ]);
    }

    /**
     * Bulk-create extinguishers for a building (identified by name).
     *
     * Body: { building_name, count, items: [{ label, installation_date, next_refill_date }] }
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.create');

        $validated = $request->validate([
            'building_name' => ['required', 'string', 'max:255'],
            'count' => ['required', 'integer', 'min:1', 'max:500'],
            'items' => ['nullable', 'array'],
            'items.*.label' => ['nullable', 'string', 'max:255'],
            'items.*.location' => ['nullable', 'string', 'max:255'],
            'items.*.type' => ['nullable', 'string', 'max:255'],
            'items.*.capacity' => ['nullable', 'integer', 'min:0'],
            'items.*.installation_date' => ['nullable', 'date'],
            'items.*.next_refill_date' => ['nullable', 'date'],
            'items.*.year_of_manufacturing' => ['nullable', 'integer'],
            'items.*.remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $building = Building::firstOrCreate(
            ['name' => trim($validated['building_name'])],
            ['name' => trim($validated['building_name'])]
        );

        $count = (int) $validated['count'];
        $items = $validated['items'] ?? [];

        $created = [];
        for ($i = 0; $i < $count; $i++) {
            $row = $items[$i] ?? [];
            $created[] = $building->fireSystems()->create([
                'system_type' => FireSystem::TYPE_EXTINGUISHER,
                'sub_type' => 'Fire Extinguisher',
                'quantity' => 1,
                'type' => $row['type'] ?? null,
                'capacity' => $row['capacity'] ?? null,
                'label' => $row['label'] ?? null,
                'location' => $row['location'] ?? null,
                'installation_date' => $row['installation_date'] ?? null,
                'next_refill_date' => $row['next_refill_date'] ?? null,
                'year_of_manufacturing' => $row['year_of_manufacturing'] ?? null,
                'remark' => $row['remark'] ?? null,
            ]);
        }

        return $this->success("{$count} extinguisher(s) added to {$building->name}", [
            'building' => ['id' => $building->id, 'name' => $building->name],
            'extinguishers' => array_map(fn ($e) => $this->present($e), $created),
        ], [], 201);
    }

    /**
     * Update a single extinguisher (label / dates).
     */
    public function update(Request $request, FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.update');

        $validated = $request->validate([
            'label' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'type' => ['nullable', 'string', 'max:255'],
            'capacity' => ['nullable', 'integer', 'min:0'],
            'installation_date' => ['nullable', 'date'],
            'next_refill_date' => ['nullable', 'date'],
            'year_of_manufacturing' => ['nullable', 'integer'],
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        $data = [
            'system_type' => FireSystem::TYPE_EXTINGUISHER,
            'sub_type' => 'Fire Extinguisher',
            'quantity' => 1,
            'label' => $validated['label'] ?? $fireSystem->label,
            'capacity' => $validated['capacity'] ?? $fireSystem->capacity,
            'installation_date' => !empty($validated['installation_date']) ? $validated['installation_date'] : null,
            'next_refill_date' => !empty($validated['next_refill_date']) ? $validated['next_refill_date'] : null,
            'location' => $validated['location'] ?? $fireSystem->location,
            'type' => $validated['type'] ?? $fireSystem->type,
            'year_of_manufacturing' => $validated['year_of_manufacturing'] ?? $fireSystem->year_of_manufacturing,
            'remark' => $validated['remark'] ?? $fireSystem->remark,
        ];

        if (array_key_exists('location', $validated) && $validated['location'] === '') {
            $data['location'] = null;
        }
        if (array_key_exists('type', $validated) && $validated['type'] === '') {
            $data['type'] = null;
        }
        if (array_key_exists('remark', $validated) && $validated['remark'] === '') {
            $data['remark'] = null;
        }
        if (array_key_exists('year_of_manufacturing', $validated)
            && ($validated['year_of_manufacturing'] === '' || $validated['year_of_manufacturing'] === null
                || $validated['year_of_manufacturing'] === '0')) {
            $data['year_of_manufacturing'] = null;
        }

        $fireSystem->update($data);

        return $this->success('Extinguisher updated', [
            'extinguisher' => $this->present($fireSystem->fresh()),
        ]);
    }

    /**
     * Delete a single extinguisher.
     */
    public function destroy(FireSystem $fireSystem): JsonResponse
    {
        $this->authorize('extinguishers.delete');

        $fireSystem->delete();

        return $this->success('Extinguisher deleted');
    }

    /**
     * List certificates attached to buildings (optionally filtered by building).
     */
    public function listCertificates(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.view');

        $query = Document::where('documentable_type', Building::class)
            ->with('uploader:id,name');

        if ($request->filled('building_id')) {
            $query->where('documentable_id', $request->integer('building_id'));
        }

        $documents = $query->orderBy('id', 'desc')->get();

        return $this->success('Certificates retrieved', [
            'certificates' => $documents->map(fn ($d) => $this->presentDocument($d))->values(),
        ]);
    }

    /**
     * Attach a certificate to a building (shared across its extinguishers).
     *
     * Body: { building_id, file, expiry_date?, remarks? }
     */
    public function uploadCertificate(Request $request): JsonResponse
    {
        $this->authorize('extinguishers.update');

        $validated = $request->validate([
            'building_id' => ['required', 'integer', 'exists:buildings,id'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx', 'max:10240'],
            'expiry_date' => ['nullable', 'date'],
            'remarks' => ['nullable', 'string', 'max:1000'],
        ]);

        $file = $request->file('file');
        $path = $file->store('extinguisher-certificates', 'public');

        $document = Document::create([
            'file_name' => $file->getClientOriginalName(),
            'original_file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_type' => $file->getClientOriginalExtension(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
            'document_type' => 'Fire Extinguisher Certificate',
            'expiry_date' => $validated['expiry_date'] ?? null,
            'remarks' => $validated['remarks'] ?? null,
            'documentable_id' => $validated['building_id'],
            'documentable_type' => Building::class,
            'uploaded_by' => Auth::id(),
        ]);

        return $this->success('Certificate uploaded', [
            'certificate' => $this->presentDocument($document->load('uploader:id,name')),
        ], [], 201);
    }

    /**
     * Download a building certificate.
     */
    public function downloadCertificate(Document $document)
    {
        $this->authorize('extinguishers.view');

        $this->ensureBuildingDocument($document);

        if (!Storage::disk('public')->exists($document->file_path)) {
            return $this->error('Certificate file not found', [], 404);
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Delete a building certificate.
     */
    public function deleteCertificate(Document $document): JsonResponse
    {
        $this->authorize('extinguishers.delete');

        $this->ensureBuildingDocument($document);

        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        $document->delete();

        return $this->success('Certificate deleted');
    }

    protected function ensureBuildingDocument(Document $document): void
    {
        if ($document->documentable_type !== Building::class) {
            abort(404, 'Certificate not found');
        }
    }

    protected function presentDocument(Document $d): array
    {
        return [
            'id' => $d->id,
            'building_id' => $d->documentable_type === Building::class ? $d->documentable_id : null,
            'file_name' => $d->file_name,
            'file_type' => $d->file_type,
            'file_size' => $d->file_size,
            'expiry_date' => $d->expiry_date?->toDateString(),
            'remarks' => $d->remarks,
            'created_at' => $d->created_at?->toDateTimeString(),
            'uploaded_by' => optional($d->uploader)->name,
        ];
    }

    protected function present(FireSystem $e): array
    {
        return [
            'id' => $e->id,
            'building_id' => $e->building_id,
            'building' => [
                'id' => $e->building_id,
                'name' => $e->building?->name ?? null,
            ],
            'label' => $e->label,
            'location' => $e->location,
            'type' => $e->type,
            'capacity' => $e->capacity !== null ? (int) $e->capacity : null,
            'installation_date' => $e->installation_date?->toDateString(),
            'next_refill_date' => $e->next_refill_date?->toDateString(),
            'year_of_manufacturing' => $e->year_of_manufacturing !== null ? (int) $e->year_of_manufacturing : null,
            'remark' => $e->remark,
            'quantity' => $e->quantity,
        ];
    }
}
