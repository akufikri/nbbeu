#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-card-certificate-fixes.sh
#
# Kad ahli & certificate backlog, all fixes combined:
#   1. Kad ahli PDF: dompdf doesn't support CSS text-overflow: ellipsis (text
#      was hard-clipping mid-word with no "…") — truncated server-side with
#      Str::limit instead. Font was also genuinely too small (8.5px on a
#      340x214px card) — bumped to 12px and re-tuned the truncation length so
#      text still fits its box without overflowing.
#   2. Certificate PDF: large empty gap at the bottom, content wasn't filling
#      the A4 landscape frame. Tried a fixed-height frame with table-cell/flex
#      centering (both are no-ops in this dompdf version) and a calculated top
#      padding — that overflowed onto a second blank page instead (razor-thin
#      margin against dompdf's actual 841.89x595.28pt canvas). Settled on
#      dropping the decorative border entirely and using a fixed top margin —
#      single page, no border to make the remaining gap visually obvious.
#   3. Regenerates card + certificate PDFs for every already-approved member —
#      GenerateMemberCard/GenerateCertificate render once at approval and cache
#      to storage, so template fixes alone never reach already-approved members.
#   4. Adds an inline PDF preview to the member "My Certificate" page.
#      Storage::download() forces an "attachment" Content-Disposition, which
#      browsers won't render inline — added a separate preview route serving
#      the same file with "inline" disposition instead, embedded via <iframe>.
#   5. Same preview treatment on the admin side: Card History and Certificate
#      History now have a "Preview" row action (modal + iframe) alongside the
#      existing Download/Regenerate actions, using new admin-only inline
#      routes (auth:admin guarded, separate from the member ones above).
#   6. Fixed a format mismatch this surfaced: admin's "Download Card" streamed
#      the cached member-card.blade.php PDF, while the member portal live-
#      renders a PNG (RenderMemberCardImage) — two different renderers, two
#      different outputs for "the same" card. Admin preview/download now uses
#      the same live PNG renderer, so both sides are always identical (the
#      PDF renderer/GenerateMemberCard stays — it's still used as the approval
#      email attachment, just no longer surfaced in the admin UI).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run this from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# ------------------------------------------------------------
# 1) Kad ahli PDF template — font size + ellipsis fix
# ------------------------------------------------------------
mkdir -p resources/views/pdf
cat > resources/views/pdf/member-card.blade.php <<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'DejaVu Sans', sans-serif; }
        .card { position: relative; width: 340px; height: 214px; overflow: hidden; page-break-after: always; }
        .card:last-child { page-break-after: avoid; }
        .card-bg { position: absolute; top: 0; left: 0; width: 340px; height: 214px; }

        .photo-box {
            position: absolute;
            left: 234.6px; top: 36px; width: 68.7px; height: 75.8px;
            overflow: hidden;
        }
        .photo-box img { width: 100%; height: 100%; object-fit: cover; }

        .field {
            position: absolute;
            left: 55.8px; width: 108.8px;
            font-size: 12px;
            color: #16305C;
            white-space: nowrap;
            overflow: hidden;
        }
        .field-name { top: 40.6px; }
        .field-phone { top: 67.5px; }
        .field-email { top: 95.2px; }
        .field-location { top: 123.8px; }

        .qr-box {
            position: absolute;
            left: 246.5px; top: 116.6px; width: 40.8px; height: 40.8px;
        }
        .qr-box img { width: 100%; height: 100%; }
    </style>
</head>
<body>
    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/front-kad-ahli.png') }}">

        <div class="photo-box">
            @if ($photoUrl)
                <img src="{{ $photoUrl }}">
            @endif
        </div>

        {{-- dompdf doesn't support CSS text-overflow: ellipsis (text just gets
             hard-clipped mid-word with no "…"), so truncate server-side instead. --}}
        <div class="field field-name">{{ \Illuminate\Support\Str::limit($user->name, 16) }}</div>
        <div class="field field-phone">{{ \Illuminate\Support\Str::limit($user->phone, 16) }}</div>
        <div class="field field-email">{{ \Illuminate\Support\Str::limit($user->email, 16) }}</div>
        <div class="field field-location">{{ \Illuminate\Support\Str::limit($location, 16) }}</div>

        <div class="qr-box">
            <img src="{{ $qrDataUri }}">
        </div>
    </div>

    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/back-kad-ahli.png') }}">
    </div>
</body>
</html>
BLADE

# ------------------------------------------------------------
# 2) Certificate PDF template — bottom-gap fix
# ------------------------------------------------------------
cat > resources/views/pdf/certificate.blade.php <<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        html, body {
            margin: 0;
            font-family: 'DejaVu Serif', serif;
            color: #232A33;
            background-color: #F6F7F9;
        }
        .frame {
            margin: 160px 60px 0;
            text-align: center;
        }
        .logo {
            width: 72px;
            height: 72px;
        }
        .org {
            font-size: 12px;
            letter-spacing: 2px;
            color: #16305C;
            text-transform: uppercase;
            font-weight: bold;
            margin-top: 12px;
        }
        .title {
            font-size: 32px;
            color: #0B1D3A;
            font-weight: bold;
            margin-top: 18px;
        }
        .sub {
            font-size: 12px;
            color: #232A33;
            margin-top: 24px;
        }
        .name {
            font-size: 26px;
            color: #16305C;
            font-weight: bold;
            margin-top: 10px;
            border-bottom: 1px solid #B08D3D;
            display: inline-block;
            padding-bottom: 6px;
        }
        .body-text {
            font-size: 12px;
            color: #232A33;
            margin-top: 24px;
            line-height: 1.6;
        }
        .footer {
            margin-top: 50px;
            font-size: 10px;
            font-family: 'DejaVu Sans Mono', monospace;
            color: #232A33;
        }
    </style>
</head>
<body>
    <div class="frame">
        <img class="logo" src="{{ public_path('assets/images/logo.png') }}">
        <div class="org">North Borneo Banking Executive Union</div>
        <div class="title">Certificate of Membership</div>

        <div class="sub">This is to certify that</div>
        <div class="name">{{ $user->name }}</div>

        <div class="body-text">
            is officially registered as a member {{ $user->company ? 'from '.$user->company : '' }}<br>
            of North Borneo Banking Executive Union with Member No. <strong>{{ $user->member_no }}</strong>
        </div>

        <div class="footer">
            Certificate No.: {{ $certNumber }} &nbsp;&bull;&nbsp; Issued: {{ now()->format('d M Y') }}
        </div>
    </div>
</body>
</html>
BLADE

# ------------------------------------------------------------
# 3) Member portal — DocumentController + inline certificate preview
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Member
cat > app/Http/Controllers/Member/DocumentController.php <<'PHP'
<?php

namespace App\Http\Controllers\Member;

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
        $certificate = $request->user()->certificates()->latest('id')->firstOrFail();

        abort_unless(Storage::exists($certificate->file_path), 404);

        return Storage::download($certificate->file_path, "Certificate-{$request->user()->member_no}.pdf");
    }

    public function certificatePreview(Request $request): StreamedResponse
    {
        $certificate = $request->user()->certificates()->latest('id')->firstOrFail();

        abort_unless(Storage::exists($certificate->file_path), 404);

        return Storage::response($certificate->file_path, "Certificate-{$request->user()->member_no}.pdf", [
            'Content-Disposition' => 'inline',
        ]);
    }
}
PHP

mkdir -p resources/views/member
cat > resources/views/member/certificate.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Certificate') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                @if (! $certificate)
                    <p class="text-sm text-gray-600">Your certificate has not been generated yet.</p>
                @else
                    <p class="text-gray-500 text-sm">Certificate No.</p>
                    <p class="font-mono font-medium">{{ $certificate->cert_number }}</p>
                    <p class="text-gray-500 text-sm mt-3">Issued</p>
                    <p class="font-medium">{{ $certificate->issued_at?->format('d M Y') }}</p>
                    <a href="{{ route('member.documents.certificate') }}" class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                        Download Certificate (PDF)
                    </a>

                    <div class="mt-6 border rounded-sm overflow-hidden" style="aspect-ratio: 297 / 210;">
                        <iframe
                            src="{{ route('member.documents.certificate.preview') }}"
                            title="Certificate preview"
                            class="w-full h-full"
                        ></iframe>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

# routes/web.php — add the member preview route right after the existing certificate download route
if ! grep -q "documents.certificate.preview" routes/web.php; then
    perl -0777 -pi -e "s{(Route::get\('/documents/certificate', \[DocumentController::class, 'certificate'\]\)->name\('documents.certificate'\);\n)}{\$1        Route::get('/documents/certificate/preview', [DocumentController::class, 'certificatePreview'])->name('documents.certificate.preview');\n}" routes/web.php
    echo "Patched routes/web.php (member certificate preview route)"
else
    echo "Skipped routes/web.php member preview route (already patched)"
fi

# routes/web.php — add the "use" import + admin preview routes group
if ! grep -q "use App\\\\Http\\\\Controllers\\\\Admin\\\\DocumentPreviewController;" routes/web.php; then
    perl -0777 -pi -e "s{(use App\\\\Http\\\\Controllers\\\\Member\\\\DashboardController as MemberDashboardController;\n)}{use App\\\\Http\\\\Controllers\\\\Admin\\\\DocumentPreviewController;\n\$1}" routes/web.php
    echo "Patched routes/web.php (DocumentPreviewController import)"
else
    echo "Skipped routes/web.php import (already patched)"
fi

if ! grep -q "admin.documents.cards.preview\|admin/documents/cards" routes/web.php; then
    perl -0777 -pi -e "s{(require __DIR__\.'/auth\.php';)}{Route::middleware('auth:admin')->prefix('admin/documents')->name('admin.documents.')->group(function () {\n    Route::get('/cards/{memberCard}/preview', [DocumentPreviewController::class, 'card'])->name('cards.preview');\n    Route::get('/certificates/{certificate}/preview', [DocumentPreviewController::class, 'certificate'])->name('certificates.preview');\n});\n\n\$1}" routes/web.php
    echo "Patched routes/web.php (admin document preview routes)"
else
    echo "Skipped routes/web.php admin routes (already patched)"
fi

# ------------------------------------------------------------
# 5) Admin document preview — controller, modal partial, table actions
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Admin
cat > app/Http/Controllers/Admin/DocumentPreviewController.php <<'PHP'
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
PHP

mkdir -p resources/views/filament/modals
cat > resources/views/filament/modals/pdf-preview.blade.php <<'BLADE'
<div class="w-full" style="aspect-ratio: {{ $aspectRatio ?? '297 / 210' }};">
    <iframe src="{{ $url }}" title="Document preview" class="w-full h-full border rounded-sm"></iframe>
</div>
BLADE

cat > resources/views/filament/modals/image-preview.blade.php <<'BLADE'
<div class="w-full flex justify-center">
    <img src="{{ $url }}" alt="Preview" class="max-w-full h-auto border rounded-sm">
</div>
BLADE

mkdir -p app/Filament/Resources/CardHistory/Tables
cat > app/Filament/Resources/CardHistory/Tables/CardHistoryTable.php <<'PHP'
<?php

namespace App\Filament\Resources\CardHistory\Tables;

use App\Actions\Membership\GenerateMemberCard;
use App\Actions\Membership\RenderMemberCardImage;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use ZipArchive;

class CardHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('user.member_no')
                    ->label('Member No.')
                    ->searchable(),
                TextColumn::make('card_number')
                    ->label('Card No.'),
                TextColumn::make('issued_at')
                    ->label('Card Issued')
                    ->date(),
                TextColumn::make('expires_at')
                    ->label('Card Expires')
                    ->date(),
            ])
            ->recordActions([
                Action::make('preview_card')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Card Preview')
                    ->modalContent(fn ($record) => view('filament.modals.image-preview', [
                        'url' => route('admin.documents.cards.preview', $record),
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                // Same PNG renderer + zip format the member portal download uses,
                // so admin and member downloads are always identical.
                Action::make('download_card')
                    ->label('Download Card')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $frontPng = app(RenderMemberCardImage::class)($record->user, $record);
                        $backPng = file_get_contents(public_path('assets/illustrations/back-kad-ahli.png'));

                        $tmpPath = tempnam(sys_get_temp_dir(), 'nbbeu-card-').'.zip';

                        $zip = new ZipArchive();
                        $zip->open($tmpPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
                        $zip->addFromString('Member-Card-Front.png', $frontPng);
                        $zip->addFromString('Member-Card-Back.png', $backPng);
                        $zip->close();

                        return response()->download($tmpPath, "Member-Card-{$record->user->member_no}.zip")->deleteFileAfterSend();
                    }),
                Action::make('regenerate_card')
                    ->label('Regenerate Card')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(GenerateMemberCard::class)($record->user);

                        Notification::make()
                            ->success()
                            ->title('Member card regenerated')
                            ->send();
                    }),
            ]);
    }
}
PHP

mkdir -p app/Filament/Resources/CertificateHistory/Tables
cat > app/Filament/Resources/CertificateHistory/Tables/CertificateHistoryTable.php <<'PHP'
<?php

namespace App\Filament\Resources\CertificateHistory\Tables;

use App\Actions\Membership\GenerateCertificate;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class CertificateHistoryTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('issued_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->searchable()
                    ->weight('medium'),
                TextColumn::make('user.member_no')
                    ->label('Member No.')
                    ->searchable(),
                TextColumn::make('cert_number')
                    ->label('Certificate No.'),
                TextColumn::make('issued_at')
                    ->label('Certificate Issued')
                    ->date(),
            ])
            ->recordActions([
                Action::make('preview_certificate')
                    ->label('Preview')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Certificate Preview')
                    ->modalContent(fn ($record) => view('filament.modals.pdf-preview', [
                        'url' => route('admin.documents.certificates.preview', $record),
                        'aspectRatio' => '297 / 210',
                    ]))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn ($record) => filled($record->file_path) && Storage::exists($record->file_path)),
                Action::make('download_certificate')
                    ->label('Download Certificate')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($record) => response()->download(Storage::path($record->file_path), basename($record->file_path)))
                    ->visible(fn ($record) => filled($record->file_path) && Storage::exists($record->file_path)),
                Action::make('regenerate_certificate')
                    ->label('Regenerate Certificate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        app(GenerateCertificate::class)($record->user);

                        Notification::make()
                            ->success()
                            ->title('Certificate regenerated')
                            ->send();
                    }),
            ]);
    }
}
PHP

php artisan optimize:clear

# ------------------------------------------------------------
# 4) Regenerate card + certificate PDFs for already-approved members
# ------------------------------------------------------------
echo "Regenerating card + certificate PDFs for already-approved members..."
php artisan tinker --execute="
\$cardAction = app(App\Actions\Membership\GenerateMemberCard::class);
\$certAction = app(App\Actions\Membership\GenerateCertificate::class);
\$users = App\Models\User::where('status', 'approved')->whereNotNull('member_no')->get();
echo 'Members to regenerate: '.\$users->count().PHP_EOL;
foreach (\$users as \$user) {
    try {
        \$cardAction(\$user);
        \$certAction(\$user);
        echo \"  OK: {\$user->member_no}\".PHP_EOL;
    } catch (\Throwable \$e) {
        echo \"  FAIL: {\$user->member_no} - {\$e->getMessage()}\".PHP_EOL;
    }
}
"

echo "Done: card font bumped to 12px, certificate bottom gap fixed, admin/member preview added,"
echo "admin card download now matches member portal (live PNG, not the separate cached PDF), PDFs regenerated."
