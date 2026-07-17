<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function cardPage(Request $request): View
    {
        $user = $request->user();
        $memberCard = $user->memberCards()->latest('id')->first();

        $qrDataUri = $memberCard
            ? (new Builder(data: route('verify.card', $memberCard->qr_token), size: 220, margin: 0))->build()->getDataUri()
            : null;

        return view('member.card', [
            'user' => $user,
            'memberCard' => $memberCard,
            'qrDataUri' => $qrDataUri,
        ]);
    }

    public function certificatePage(Request $request): View
    {
        return view('member.certificate', [
            'user' => $request->user(),
            'certificate' => $request->user()->certificates()->latest('id')->first(),
        ]);
    }

    public function card(Request $request): StreamedResponse
    {
        $memberCard = $request->user()->memberCards()->latest('id')->firstOrFail();

        abort_unless(Storage::exists($memberCard->file_path), 404);

        return Storage::download($memberCard->file_path, "Member-Card-{$request->user()->member_no}.pdf");
    }

    public function certificate(Request $request): StreamedResponse
    {
        $certificate = $request->user()->certificates()->latest('id')->firstOrFail();

        abort_unless(Storage::exists($certificate->file_path), 404);

        return Storage::download($certificate->file_path, "Certificate-{$request->user()->member_no}.pdf");
    }
}
