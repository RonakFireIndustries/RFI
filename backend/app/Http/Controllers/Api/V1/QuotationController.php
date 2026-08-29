<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Quotation;
use App\Models\QuotationItem;
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

        $query = Quotation::with(['building:id,name', 'items']);

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
            'quotations' => $quotations->items(),
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
        $quotation->load(['building:id,name', 'items.product:id,name,unit', 'creator:id,name']);
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.unit' => ['nullable', 'string', 'max:100'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
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

            foreach ($validated['items'] as $row) {
                $description = $row['description'] ?? null;
                if (empty($description) && !empty($row['product_id'])) {
                    $description = optional(\App\Models\Product::find($row['product_id']))->name;
                }
                $qty = (float) $row['qty'];
                $rate = (float) $row['rate'];
                $quotation->items()->create([
                    'product_id' => $row['product_id'] ?? null,
                    'description' => $description,
                    'unit' => $row['unit'] ?? null,
                    'qty' => $qty,
                    'rate' => $rate,
                    'amount' => round($qty * $rate, 2),
                ]);
            }

            return $quotation->fresh(['building:id,name', 'items']);
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
            'items' => ['required', 'array', 'min:1'],
            'items.*.id' => ['nullable', 'exists:quotation_items,id'],
            'items.*.product_id' => ['nullable', 'exists:products,id'],
            'items.*.description' => ['nullable', 'string', 'max:500'],
            'items.*.unit' => ['nullable', 'string', 'max:100'],
            'items.*.qty' => ['required', 'numeric', 'min:0.01'],
            'items.*.rate' => ['required', 'numeric', 'min:0'],
        ]);

        $quotation = DB::transaction(function () use ($request, $quotation, $validated) {
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

            $incomingIds = [];
            foreach ($validated['items'] as $row) {
                $description = $row['description'] ?? null;
                if (empty($description) && !empty($row['product_id'])) {
                    $description = optional(\App\Models\Product::find($row['product_id']))->name;
                }
                $data = [
                    'product_id' => $row['product_id'] ?? null,
                    'description' => $description,
                    'unit' => $row['unit'] ?? null,
                    'qty' => (float) $row['qty'],
                    'rate' => (float) $row['rate'],
                    'amount' => round((float) $row['qty'] * (float) $row['rate'], 2),
                ];

                if (!empty($row['id'])) {
                    $item = QuotationItem::where('quotation_id', $quotation->id)
                        ->whereKey($row['id'])->first();
                    if ($item) {
                        $item->update($data);
                        $incomingIds[] = $item->id;
                        continue;
                    }
                }
                $new = $quotation->items()->create($data);
                $incomingIds[] = $new->id;
            }

            $quotation->items()->whereNotIn('id', $incomingIds)->delete();

            return $quotation->fresh(['building:id,name', 'items']);
        });

        return $this->success('Quotation updated', ['quotation' => $this->present($quotation)]);
    }

    public function destroy(Quotation $quotation): JsonResponse
    {
        $this->authorize('quotations.delete');
        $quotation->items()->delete();
        $quotation->delete();
        return $this->success('Quotation deleted');
    }

    /**
     * Download the BOQ as a server-rendered PDF.
     */
    public function pdf(Quotation $quotation)
    {
        $this->authorize('quotations.view');
        $quotation->load(['building:id,name', 'items', 'creator:id,name']);

        $pdf = Pdf::loadView('pdf.quotation', ['quotation' => $quotation])
            ->setPaper('a4');

        return $pdf->download("BOQ_{$quotation->quotation_no}.pdf");
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
            'items' => $q->items->map(fn ($i) => [
                'id' => $i->id,
                'product_id' => $i->product_id,
                'description' => $i->description,
                'unit' => $i->unit,
                'qty' => (float) $i->qty,
                'rate' => (float) $i->rate,
                'amount' => (float) $i->amount,
            ])->values(),
        ];
    }
}
