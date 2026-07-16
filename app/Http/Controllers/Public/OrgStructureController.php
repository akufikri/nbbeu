<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrgChart;
use Illuminate\View\View;

class OrgStructureController extends Controller
{
    public function index(): View
    {
        $orgChart = OrgChart::where('is_active', true)
            ->orderBy('display_order')
            ->get();

        return view('public.org-structure', ['orgChart' => $orgChart]);
    }
}
