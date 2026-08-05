<?php

namespace App\Http\Controllers\Admin;

use App\Actions\Membership\RenderMemberCardImage;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\MemberCard;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentPreviewController extends Controller
{
    /**
     * Live-rendered PNG, same renderer the member portal download uses —
     * keeps admin preview/download identical to what members actually get,
     * instead of the separate (and previously out-of-sync) cached PDF.
     */
    public function card(MemberCard $memberCard, RenderMemberCardImage $renderMemberCardImage): Response
    {
        $png = $renderMemberCardImage($memberCard->user, $memberCard);

        return response($png, 200, ['Content-Type' => 'image/png']);
    }

    public function certificate(Certificate $certificate): StreamedResponse
    {
        abort_unless(filled($certificate->file_path) && Storage::exists($certificate->file_path), 404);

        return Storage::response($certificate->file_path, basename($certificate->file_path), [
            'Content-Disposition' => 'inline',
        ]);
    }
}
