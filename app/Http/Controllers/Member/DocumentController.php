<?php

namespace App\Http\Controllers\Member;

use App\Actions\Membership\GenerateCertificate;
use App\Actions\Membership\RenderMemberCardImage;
use App\Http\Controllers\Controller;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

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
            'photoUrl' => $user->photo ? Storage::disk('cloudinary')->url($user->photo) : null,
            'location' => $user->memberProfile?->residential_address ?? '-',
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

        $frontPng = $renderMemberCardImage($user, $memberCard);
        $backPng = file_get_contents(public_path('assets/illustrations/back-kad-ahli.png'));

        $tmpPath = tempnam(sys_get_temp_dir(), 'nbbeu-card-').'.zip';

        $zip = new ZipArchive();
        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('Member-Card-Front.png', $frontPng);
        $zip->addFromString('Member-Card-Back.png', $backPng);
        $zip->close();

        return response()->download($tmpPath, "Member-Card-{$user->member_no}.zip")->deleteFileAfterSend();
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
