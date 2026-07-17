<?php

namespace App\Http\Controllers\Member;

use App\Actions\Membership\GenerateUnionDuesConsentLetter;
use App\Http\Controllers\Controller;
use App\Models\UnionDuesMandate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UnionDuesController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('member.union-dues', [
            'mandate' => $user->unionDuesMandates()->latest('id')->first(),
            'hasMemberProfile' => $user->memberProfile !== null,
        ]);
    }

    public function store(Request $request, GenerateUnionDuesConsentLetter $generateConsentLetter): RedirectResponse
    {
        $data = $request->validate([
            'deduction_amount' => ['required', 'numeric', 'min:1'],
        ]);

        $user = $request->user();

        $mandate = UnionDuesMandate::create([
            'user_id' => $user->id,
            'deduction_amount' => $data['deduction_amount'],
            'status' => 'pending_submission',
            'consent_signed_at' => now(),
        ]);

        $mandate->update([
            'consent_file_path' => $generateConsentLetter($user, $mandate),
        ]);

        return redirect()->route('member.union-dues')->with('status', 'union-dues-created');
    }

    public function download(UnionDuesMandate $mandate): StreamedResponse
    {
        abort_unless($mandate->user_id === auth()->id(), 403);

        return Storage::download($mandate->consent_file_path);
    }
}
