<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
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
