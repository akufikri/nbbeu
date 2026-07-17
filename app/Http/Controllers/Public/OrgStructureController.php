<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrgChart;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class OrgStructureController extends Controller
{
    public function index(): View
    {
        $orgChart = OrgChart::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        $roots = $orgChart->whereNull('parent_id');

        return view('public.org-structure', [
            'orgChart' => $orgChart,
            'orgChartTree' => $roots->map(fn ($person) => $this->toNode($person, $orgChart))->values(),
        ]);
    }

    protected function toNode(OrgChart $person, $all): array
    {
        $children = $all->where('parent_id', $person->id)->sortBy('display_order');

        return [
            'name' => $person->name,
            'title' => $person->position,
            'img' => $person->photo ? Storage::disk('cloudinary')->url($person->photo) : null,
            'children' => $children->map(fn ($child) => $this->toNode($child, $all))->values(),
        ];
    }
}
