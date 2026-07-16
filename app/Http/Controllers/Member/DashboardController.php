<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('member.dashboard', [
            'user' => $user,
            'memberCard' => $user->memberCards()->latest('id')->first(),
            'certificate' => $user->certificates()->latest('id')->first(),
            'latestPayment' => $user->payments()->latest('id')->first(),
            'renewalDue' => $user->renewal_expires_at && $user->renewal_expires_at->lte(now()->addDays(30)),
            'daysUntilExpiry' => $user->renewal_expires_at ? (int) now()->diffInDays($user->renewal_expires_at, false) : null,
        ]);
    }
}
