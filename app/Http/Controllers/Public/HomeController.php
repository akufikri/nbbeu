<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrgChart;
use App\Models\Post;
use App\Models\User;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        $orgChart = OrgChart::where('is_active', true)
            ->orderBy('display_order')
            ->limit(4)
            ->get();

        $latestPosts = Post::where('status', 'published')
            ->latest('published_at')
            ->limit(3)
            ->get();

        $approvedMembersCount = User::where('status', 'approved')->count();
        $memberCompaniesCount = User::where('status', 'approved')->distinct('company')->count('company');
        $leadershipCount = $orgChart->count();

        return view('public.home', [
            'orgChart' => $orgChart,
            'latestPosts' => $latestPosts,
            'approvedMembersCount' => $approvedMembersCount,
            'memberCompaniesCount' => $memberCompaniesCount,
            'leadershipCount' => $leadershipCount,
        ]);
    }
}
