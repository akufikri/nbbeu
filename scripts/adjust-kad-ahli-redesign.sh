#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-kad-ahli-redesign.sh
#
# Kad Ahli redesign — new portrait card design from lead:
#   1. New card template image (front-kad-ahli-new.png, 845x985) cropped from
#      the square template provided by lead — black border removed, clean portrait.
#   2. RenderMemberCardImage (GD PNG renderer) rewritten: loads the new template
#      as base canvas, overlays member name/ID/website at calibrated positions
#      in the cream section (y≥656). No more background-image drawing in code.
#   3. PDF template (member-card.blade.php): uses new portrait image as background,
#      absolute-positioned text overlay. Page size changed to 253x295pt to match
#      new image aspect ratio (845:985 / 3.34).
#   4. GenerateMemberCard: paper size updated to [0,0,253,295], photo/QR/location
#      params removed (new design shows only name, member ID, website).
#   5. Member card download: changed from ZIP (front+back PNG) to single PNG
#      download. Back card removed per lead instruction.
#   6. Web card preview (member/card.blade.php): shows new template image with
#      CSS text overlay in cream section.
#   7. Regenerates card PDFs + PNGs for all already-approved members so they get
#      the new design without needing re-approval.
#
# PREREQUISITE: front-kad-ahli-new.png must be in public/assets/illustrations/
# (committed to git and pulled before running this script on the server).
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
if [ ! -f "$APP_ROOT/artisan" ]; then
    echo "Could not locate artisan (looked upward from $SCRIPT_DIR). Run from inside the Laravel project." >&2
    exit 1
fi
cd "$APP_ROOT"

# Guard: require new template image
if [ ! -f "public/assets/illustrations/front-kad-ahli-new.png" ]; then
    echo "ERROR: public/assets/illustrations/front-kad-ahli-new.png not found." >&2
    echo "Run 'git pull' first — the image must be committed and pulled before this script." >&2
    exit 1
fi

# ------------------------------------------------------------
# 1) RenderMemberCardImage — GD renderer using new template
# ------------------------------------------------------------
mkdir -p app/Actions/Membership
cat > app/Actions/Membership/RenderMemberCardImage.php <<'PHP'
<?php

namespace App\Actions\Membership;

use App\Models\MemberCard;
use App\Models\User;
use Illuminate\Support\Str;

/**
 * Renders the front of the member card as a portrait PNG.
 * Uses front-kad-ahli-new.png (845×985) as the base template and overlays
 * dynamic member text into the cream section (y≥656).
 */
class RenderMemberCardImage
{
    public function __invoke(User $user, MemberCard $memberCard): string
    {
        $canvas = imagecreatefrompng(public_path('assets/illustrations/front-kad-ahli-new.png'));

        $brown  = imagecolorallocate($canvas, 0x3B, 0x25, 0x00);
        $dbrown = imagecolorallocate($canvas, 0x5C, 0x3D, 0x00);

        $bold    = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans-Bold.ttf');
        $regular = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');

        $name = strtoupper($user->name);
        $this->drawCentered($canvas, $bold,    26, $brown,  Str::limit($name, 30, ''), 845, 723);
        $this->drawCentered($canvas, $regular, 21, $brown,  'Member ID '.$memberCard->card_number, 845, 778);
        $this->drawCentered($canvas, $regular, 17, $dbrown, 'www.nbbeu.org.my', 845, 838);

        ob_start();
        imagepng($canvas);
        $output = ob_get_clean();
        imagedestroy($canvas);

        return $output;
    }

    private function drawCentered($canvas, string $font, int $size, int $color, string $text, int $canvasW, int $y): void
    {
        $bbox  = imagettfbbox($size, 0, $font, $text);
        $textW = $bbox[2] - $bbox[0];
        $x     = (int) (($canvasW - $textW) / 2);
        imagettftext($canvas, $size, 0, $x, $y, $color, $font, $text);
    }
}
PHP

# ------------------------------------------------------------
# 2) PDF template — portrait image + text overlay
# ------------------------------------------------------------
mkdir -p resources/views/pdf
cat > resources/views/pdf/member-card.blade.php <<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'DejaVu Sans', sans-serif; }
        .card { position: relative; width: 253px; height: 295px; overflow: hidden; }
        .card-bg { position: absolute; top: 0; left: 0; width: 253px; height: 295px; }
        .field {
            position: absolute;
            left: 0; width: 253px;
            text-align: center;
            color: #3B2500;
            white-space: nowrap;
            overflow: hidden;
        }
        .f-name { top: 204px; font-size: 9px; font-weight: bold; letter-spacing: 0.5px; }
        .f-id   { top: 225px; font-size: 8px; }
        .f-web  { top: 244px; font-size: 7px; color: #5C4000; }
    </style>
</head>
<body>
    <div class="card">
        <img class="card-bg" src="{{ public_path('assets/illustrations/front-kad-ahli-new.png') }}">
        {{-- dompdf clips hard — truncate server-side --}}
        <div class="field f-name">{{ Str::upper(Str::limit($user->name, 30, '')) }}</div>
        <div class="field f-id">Member ID {{ $memberCard->card_number }}</div>
        <div class="field f-web">www.nbbeu.org.my</div>
    </div>
</body>
</html>
BLADE

# ------------------------------------------------------------
# 3) GenerateMemberCard — portrait paper size, simplified data
# ------------------------------------------------------------
cat > app/Actions/Membership/GenerateMemberCard.php <<'PHP'
<?php

namespace App\Actions\Membership;

use App\Models\MemberCard;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Render the member card (PDF) + qr_token for public verification.
 * See DATABASE.md §4, ROADMAP.md Phase 3, TRD.md §3.3-3.4.
 */
class GenerateMemberCard
{
    public function __invoke(User $user): MemberCard
    {
        $qrToken = Str::random(40);

        $memberCard = MemberCard::updateOrCreate(
            ['user_id' => $user->id],
            [
                'card_number' => $user->member_no,
                'qr_token' => $qrToken,
                'issued_at' => now()->toDateString(),
                'expires_at' => $user->renewal_expires_at ?? now()->addYear(),
            ]
        );

        $pdf = app('dompdf.wrapper')
            ->loadView('pdf.member-card', [
                'user' => $user,
                'memberCard' => $memberCard,
            ])
            ->setPaper([0, 0, 253, 295]);

        $filePath = "cards/{$user->member_no}.pdf";
        Storage::put($filePath, $pdf->output());

        $memberCard->forceFill(['file_path' => $filePath])->save();

        return $memberCard;
    }
}
PHP

# ------------------------------------------------------------
# 4) DocumentController — single PNG download, no ZIP, no back card
# ------------------------------------------------------------
mkdir -p app/Http/Controllers/Member
cat > app/Http/Controllers/Member/DocumentController.php <<'PHP'
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
PHP

# ------------------------------------------------------------
# 5) Web card preview — image template + CSS text overlay
# ------------------------------------------------------------
mkdir -p resources/views/member
cat > resources/views/member/card.blade.php <<'BLADE'
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Member Card') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
                @if (! $memberCard)
                    <p class="text-sm text-gray-600">Your member card has not been generated yet.</p>
                @else
                    <div class="flex flex-col items-center">
                        {{-- Card preview: image template + text overlay --}}
                        <div class="relative w-64" style="aspect-ratio: 845 / 985;">
                            <img src="{{ asset('assets/illustrations/front-kad-ahli-new.png') }}"
                                 alt="Member card"
                                 class="w-full h-full object-cover rounded-xl shadow-xl">
                            {{-- Text overlay in cream section (~top 66.6%) --}}
                            <div class="absolute inset-x-0 flex flex-col items-center text-center px-3"
                                 style="top: 69.5%; gap: 4%;">
                                <p class="font-bold leading-tight text-xs tracking-wide" style="color:#3B2500;">
                                    {{ strtoupper($user->name) }}
                                </p>
                                <p class="text-xs" style="color:#3B2500; margin-top:4%;">
                                    Member ID {{ $memberCard->card_number }}
                                </p>
                                <p class="text-xs" style="color:#5C4000; margin-top:4%;">
                                    www.nbbeu.org.my
                                </p>
                            </div>
                        </div>

                        <div class="mt-6 grid sm:grid-cols-2 gap-4 text-sm w-64">
                            <div>
                                <p class="text-gray-500">Member No.</p>
                                <p class="font-mono font-medium">{{ $memberCard->card_number }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500">Valid Until</p>
                                <p class="font-medium">{{ $memberCard->expires_at?->format('d M Y') }}</p>
                            </div>
                        </div>

                        <a href="{{ route('member.documents.card') }}"
                           class="mt-4 inline-block px-4 py-2 text-sm rounded-sm bg-nbbeu-navy text-white hover:bg-nbbeu-gold hover:text-nbbeu-navy-deep">
                            Download Member Card
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
BLADE

# ------------------------------------------------------------
# 6) Clear caches
# ------------------------------------------------------------
php artisan optimize:clear

# ------------------------------------------------------------
# 7) Regenerate cards for all approved members (new design)
# ------------------------------------------------------------
echo "Regenerating member cards for all approved members..."
php artisan tinker --execute="
\$action = app(App\Actions\Membership\GenerateMemberCard::class);
\$users = App\Models\User::where('status', 'approved')->whereNotNull('member_no')->get();
echo 'Total: '.\$users->count().PHP_EOL;
foreach (\$users as \$user) {
    try {
        \$action(\$user);
        echo '  OK: '.\$user->member_no.PHP_EOL;
    } catch (\Throwable \$e) {
        echo '  FAIL: '.\$user->member_no.' — '.\$e->getMessage().PHP_EOL;
    }
}
"

echo ""
echo "Done: kad ahli redesign applied. New portrait card with template image."
echo "Text positions (y) in PNG: name=723, member_id=778, website=838 — adjust in"
echo "RenderMemberCardImage.php if text appears off after visual check."
