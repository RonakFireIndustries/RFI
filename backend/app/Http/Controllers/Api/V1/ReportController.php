<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    use ApiResponse;

    public function buildingReport(Request $request, Building $building): \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
    {
        $this->authorize('building.view');

        $building->load([
            'wings', 'statuses', 'fireSystems', 'contacts',
            'siteVisits.photos', 'siteVisits.voiceNotes',
            'opportunities.workTypes',
        ]);

        $format = $request->input('format', 'pdf');

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('reports.building', compact('building'));
            $pdf->setPaper('a4', 'portrait');

            return $pdf->download("building-report-{$building->id}.pdf");
        }

        return $this->success('Building report data', ['building' => $building]);
    }

    public function opportunityReport(Request $request): JsonResponse
    {
        $this->authorize('building.view');

        $query = \App\Models\Opportunity::with(['building', 'user', 'workTypes']);

        if ($request->filled('stage')) {
            $query->where('stage', $request->stage);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $opportunities = $query->get();

        $summary = [
            'total_opportunities' => $opportunities->count(),
            'total_value' => $opportunities->sum('estimated_value'),
            'won_value' => $opportunities->where('stage', 'Won')->sum('actual_final_value'),
            'lost_count' => $opportunities->where('stage', 'Lost')->count(),
            'by_stage' => $opportunities->groupBy('stage')->map(fn ($items) => [
                'count' => $items->count(),
                'value' => $items->sum('estimated_value'),
            ]),
        ];

        return $this->success('Opportunity report', [
            'summary' => $summary,
            'opportunities' => $opportunities,
        ]);
    }
}
