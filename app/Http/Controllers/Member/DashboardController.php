<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('member.dashboard', [
            'user' => $user,
            'latestPayment' => $user->payments()->latest('id')->first(),
            'renewalDue' => $user->renewal_expires_at && $user->renewal_expires_at->lte(now()->addDays(30)),
            'latestPosts' => Post::where('status', 'published')->latest('published_at')->limit(3)->get(),
        ]);
    }
}
