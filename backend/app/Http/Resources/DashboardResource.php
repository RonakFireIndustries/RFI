<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'cards' => collect($this->resource['cards'] ?? [])->map(fn($card) => [
                'key' => $card['key'],
                'name' => $card['name'],
                'icon' => $card['icon'],
                'value' => $card['value'],
                'subtitle' => $card['subtitle'] ?? null,
                'prefix' => $card['prefix'] ?? null,
                'trend' => $card['trend'] ?? null,
            ])->values()->toArray(),

            'charts' => collect($this->resource['charts'] ?? [])->map(fn($chart) => [
                'key' => $chart['key'],
                'name' => $chart['name'],
                'icon' => $chart['icon'],
                'chart_type' => $chart['chart_type'],
                'data' => $chart['data'] ?? [],
            ])->values()->toArray(),

            'quick_actions' => collect($this->resource['quick_actions'] ?? [])->map(fn($action) => [
                'key' => $action['key'],
                'name' => $action['name'],
                'icon' => $action['icon'],
                'label' => $action['label'],
                'link' => $action['link'],
                'permission' => $action['permission'] ?? null,
            ])->values()->toArray(),

            'alerts' => collect($this->resource['alerts'] ?? [])->map(fn($alert) => [
                'key' => $alert['key'],
                'name' => $alert['name'],
                'icon' => $alert['icon'],
                'value' => $alert['value'],
                'subtitle' => $alert['subtitle'] ?? null,
                'severity' => $alert['severity'] ?? 'info',
            ])->values()->toArray(),
        ];
    }
}
