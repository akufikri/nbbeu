<?php

namespace App\Http\Controllers\Member;

use App\Actions\Membership\GenerateCertificate;
use App\Actions\Membership\RenderMemberCardImage;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentController extends Controller
{
    public function cardPage(Request $request): View
    {
        $user = $request->user();

        return view('member.card', [
            'user' => $user,
            'memberCard' => $user->memberCards()->latest('id')->first(),
        ]);
    }

    public function certificatePage(Request $request): View
    {
        return view('member.certificate', [
            'user' => $request->user(),
            'certificate' => $request->user()->certificates()->latest('id')->first(),
        ]);
    }

    public function card(Request $request, RenderMemberCardImage $renderMemberCardImage): Response
    {
        $user = $request->user();
        $memberCard = $user->memberCards()->latest('id')->firstOrFail();

        $png = $renderMemberCardImage($user, $memberCard);

        return response($png, 200, [
            'Content-Type'        => 'image/png',
            'Content-Disposition' => 'attachment; filename="Member-Card-'.$user->member_no.'.png"',
        ]);
    }

    public function certificate(Request $request): StreamedResponse
    {
        $user = $request->user();
        $certificate = $user->certificates()->latest('id')->firstOrFail();

        if (! Storage::exists($certificate->file_path)) {
            $certificate = app(GenerateCertificate::class)($user);
        }

        return Storage::download($certificate->file_path, "Certificate-{$user->member_no}.pdf");
    }

    public function certificatePreview(Request $request): StreamedResponse
    {
        $user = $request->user();
        $certificate = $user->certificates()->latest('id')->firstOrFail();

        if (! Storage::exists($certificate->file_path)) {
            $certificate = app(GenerateCertificate::class)($user);
        }

        return Storage::response($certificate->file_path, "Certificate-{$user->member_no}.pdf", [
            'Content-Disposition' => 'inline',
        ]);
    }
}
