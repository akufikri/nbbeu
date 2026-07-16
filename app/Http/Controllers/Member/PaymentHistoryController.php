<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentHistoryController extends Controller
{
    public function index(Request $request): View
    {
        return view('member.payments', [
            'payments' => $request->user()->payments()->latest('id')->paginate(15),
        ]);
    }
}
