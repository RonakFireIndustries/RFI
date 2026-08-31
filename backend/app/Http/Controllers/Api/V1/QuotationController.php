<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\QuotationSection;
use App\Traits\ApiResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $this->authorize('quotations.view');

        $query = Quotation::with(['building:id,name', 'items', 'sections.items']);

        if ($request->filled('search')) {
            $s = $request->string('search')->toString();
            $query->where(function ($q) use ($s) {
                $q->where('quotation_no', 'like', "%{$s}%")
                    ->orWhere('building_name', 'like', "%{$s}%")
                    ->orWhereHas('building', fn ($b) => $b->where('name', 'like', "%{$s}%"));
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        $quotations = $query->orderBy('id', 'desc')
            ->paginate($request->integer('per_page', 20));

        return $this->success('Quotations retrieved', [
            'quotations' => array_map(fn ($q) => $this->present($q), $quotations->items()),
            'pagination' => [
                'total' => $quotations->total(),
                'per_page' => $quotations->perPage(),
                'current_page' => $quotations->currentPage(),
                'last_page' => $quotations->lastPage(),
            ],
        ]);
    }

    public function show(Quotation $quotation): JsonResponse
    {
        $this->authorize('quotations.view');
        $quotation->load(['building:id,name', 'items.product:id,name,unit', 'sections.items.product:id,name,unit,sku,product_code,dimension', 'creator:id,name']);
        return $this->success('Quotation retrieved', ['quotation' => $this->present($quotation)]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('quotations.create');

        $validated = $request->validate([
            'building_id' => ['nullable', 'exists:buildings,id'],
            'building_name' => ['nullable', 'string', 'max:255'],
            'quotation_date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted,rejected'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.name' => ['required', 'string', 'max:255'],
            'sections.*.items' => ['required', 'array', 'min:1'],
            'sections.*.items.*.product_id' => ['nullable', 'exists:products,id'],
            'sections.*.items.*.description' => ['nullable', 'string', 'max:500'],
            'sections.*.items.*.unit' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'sections.*.items.*.rate' => ['required', 'numeric', 'min:0'],
        ]);

        $quotation = DB::transaction(function () use ($request, $validated) {
            $quotation = Quotation::create([
                'quotation_no' => $this->nextQuotationNo(),
                'building_id' => $validated['building_id'] ?? null,
                'building_name' => $validated['building_name'] ?? null,
                'quotation_date' => $validated['quotation_date'],
                'status' => $validated['status'] ?? Quotation::STATUS_DRAFT,
                'discount' => $validated['discount'] ?? 0,
                'gst_percent' => $validated['gst_percent'] ?? 0,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'created_by' => $request->user()?->id,
            ]);

            $this->storeSections($quotation, $validated['sections']);

            return $quotation->fresh(['building:id,name', 'sections.items']);
        });

        return $this->success('Quotation created', ['quotation' => $this->present($quotation)], [], 201);
    }

    public function update(Request $request, Quotation $quotation): JsonResponse
    {
        $this->authorize('quotations.update');

        $validated = $request->validate([
            'building_id' => ['nullable', 'exists:buildings,id'],
            'building_name' => ['nullable', 'string', 'max:255'],
            'quotation_date' => ['required', 'date'],
            'status' => ['nullable', 'string', 'in:draft,sent,accepted,rejected'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'gst_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'terms' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'sections' => ['required', 'array', 'min:1'],
            'sections.*.id' => ['nullable', 'exists:quotation_sections,id'],
            'sections.*.name' => ['required', 'string', 'max:255'],
            'sections.*.items' => ['required', 'array', 'min:1'],
            'sections.*.items.*.id' => ['nullable', 'exists:quotation_items,id'],
            'sections.*.items.*.product_id' => ['nullable', 'exists:products,id'],
            'sections.*.items.*.description' => ['nullable', 'string', 'max:500'],
            'sections.*.items.*.unit' => ['nullable', 'string', 'max:100'],
            'sections.*.items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'sections.*.items.*.rate' => ['required', 'numeric', 'min:0'],
        ]);

        $quotation = DB::transaction(function () use ($quotation, $validated) {
            $quotation->update([
                'building_id' => $validated['building_id'] ?? null,
                'building_name' => $validated['building_name'] ?? null,
                'quotation_date' => $validated['quotation_date'],
                'status' => $validated['status'] ?? $quotation->status,
                'discount' => $validated['discount'] ?? 0,
                'gst_percent' => $validated['gst_percent'] ?? 0,
                'terms' => $validated['terms'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            $this->syncSections($quotation, $validated['sections']);

            return $quotation->fresh(['building:id,name', 'sections.items']);
        });

        return $this->success('Quotation updated', ['quotation' => $this->present($quotation)]);
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $this->authorize('quotations.delete');
        $quotation->delete();
        return $this->success('Quotation deleted');
    }

    /**
     * Download the BOQ as a server-rendered PDF.
     */
    public function pdf(Quotation $quotation)
    {
        $this->authorize('quotations.view');
        $quotation->load(['building:id,name', 'sections.items.product:id,name,unit,sku,product_code,dimension', 'creator:id,name']);

        $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $quotation, 'sections' => $quotation->sections])
            ->setPaper('a4');

        return $pdf->download("BOQ_{$quotation->quotation_no}.pdf");
    }

    protected function storeSections(Quotation $quotation, array $sections): void
    {
        foreach ($sections as $sort => $section) {
            $sectionModel = $quotation->sections()->create([
                'name' => $section['name'],
                'sort_order' => $sort,
            ]);
            $this->syncItems($sectionModel, $section['items'] ?? []);
        }
    }

    protected function syncSections(Quotation $quotation, array $sections): void
    {
        $keepSectionIds = [];
        $sort = 0;

        foreach ($sections as $section) {
            $sectionData = ['name' => $section['name'], 'sort_order' => $sort++];

            if (!empty($section['id'])) {
                $existing = QuotationSection::where('quotation_id', $quotation->id)
                    ->whereKey($section['id'])->first();
                if ($existing) {
                    $existing->update($sectionData);
                    $this->syncItems($existing, $section['items'] ?? []);
                    $keepSectionIds[] = $existing->id;
                    continue;
                }
            }

            $created = $quotation->sections()->create($sectionData);
            $this->syncItems($created, $section['items'] ?? []);
            $keepSectionIds[] = $created->id;
        }

        $quotation->sections()
            ->whereKey($keepSectionIds === [] ? [0] : $keepSectionIds)
            ->whereNotIn('id', $keepSectionIds)
            ->delete();
    }

    protected function syncItems(QuotationSection $section, array $items): void
    {
        $keepIds = [];

        foreach ($items as $row) {
            $data = $this->itemData($row);

            if (!empty($row['id'])) {
                $item = QuotationItem::where('quotation_section_id', $section->id)
                    ->whereKey($row['id'])->first();
                if ($item) {
                    $item->update($data);
                    $keepIds[] = $item->id;
                    continue;
                }
            }

            $new = $section->items()->create(array_merge($data, [
                'quotation_id' => $section->quotation_id,
            ]));
            $keepIds[] = $new->id;
        }

        if ($keepIds !== []) {
            $section->items()->whereNotIn('id', $keepIds)->delete();
        } else {
            $section->items()->delete();
        }
    }

    protected function itemData(array $row): array
    {
        $description = $row['description'] ?? null;
        if (empty($description) && !empty($row['product_id'])) {
            $description = optional(Product::find($row['product_id']))->name;
        }
        $qty = (float) $row['qty'];
        $rate = (float) $row['rate'];
        return [
            'product_id' => $row['product_id'] ?? null,
            'description' => $description,
            'unit' => $row['unit'] ?? null,
            'qty' => $qty,
            'rate' => $rate,
            'amount' => round($qty * $rate, 2),
        ];
    }

    /**
     * Generate a quoted reference number: BOQ-YYMMDD-XXXX
     */
    protected function nextQuotationNo(): string
    {
        $prefix = 'BOQ-' . now()->format('ymd') . '-';
        $last = Quotation::where('quotation_no', 'like', $prefix . '%')
            ->orderByDesc('quotation_no')
            ->value('quotation_no');

        $seq = 1;
        if ($last) {
            $parts = explode('-', $last);
            $seq = ((int) end($parts)) + 1;
        }

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    protected function present(Quotation $q): array
    {
        $sections = $q->sections->map(fn ($section) => [
            'id' => $section->id,
            'name' => $section->name,
            'sort_order' => $section->sort_order,
            'subtotal' => round($section->items->sum('amount'), 2),
            'items' => $section->items->map(fn ($i) => $this->itemBrief($i))->values(),
        ])->values();

        return [
            'id' => $q->id,
            'quotation_no' => $q->quotation_no,
            'building_id' => $q->building_id,
            'building_name' => $q->building_name,
            'display_building_name' => $q->display_building_name,
            'quotation_date' => $q->quotation_date?->toDateString(),
            'status' => $q->status,
            'discount' => $q->discount !== null ? (float) $q->discount : null,
            'gst_percent' => $q->gst_percent !== null ? (float) $q->gst_percent : null,
            'terms' => $q->terms,
            'notes' => $q->notes,
            'created_by' => $q->creator?->name,
            'subtotal' => round($q->subtotal, 2),
            'grand_total' => round($q->grand_total, 2),
            'item_count' => $q->items->count(),
            'sections' => $sections,
        ];
    }

    protected function itemBrief(QuotationItem $i): array
    {
        $product = $i->relationLoaded('product') ? $i->product : null;
        return [
            'id' => $i->id,
            'product_id' => $i->product_id,
            'description' => $i->description,
            'unit' => $i->unit,
            'qty' => (float) $i->qty,
            'rate' => (float) $i->rate,
            'amount' => (float) $i->amount,
            'product_sku' => $product?->sku,
            'product_dimension' => $product?->dimension,
        ];
    }
}
