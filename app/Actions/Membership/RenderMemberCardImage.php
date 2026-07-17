<?php

namespace App\Actions\Membership;

use App\Models\MemberCard;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Illuminate\Support\Facades\Storage;

/**
 * Renders the front of the member card as a flat PNG snapshot (as opposed
 * to GenerateMemberCard's PDF, which is the official printable document).
 * Coordinates mirror resources/views/pdf/member-card.blade.php and
 * resources/views/member/card.blade.php, scaled up to the illustration's
 * native 1594x987 resolution.
 */
class RenderMemberCardImage
{
    public function __invoke(User $user, MemberCard $memberCard): string
    {
        $canvas = imagecreatefrompng(public_path('assets/illustrations/front-kad-ahli.png'));
        $navy = imagecolorallocate($canvas, 0x16, 0x30, 0x5C);
        $font = base_path('vendor/dompdf/dompdf/lib/fonts/DejaVuSans.ttf');

        if ($user->photo) {
            $this->drawPhoto($canvas, Storage::disk('cloudinary')->get($user->photo), 1099.9, 166.0, 323.6, 349.6);
        }

        $this->drawQr($canvas, $memberCard, 1155.6, 537.9, 191.3);

        $textLeft = 261.6;
        $textWidth = 510.1;
        $fontSize = 22;
        $this->drawText($canvas, $font, $fontSize, $navy, $user->name, $textLeft, 172.7 + $fontSize, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->phone, $textLeft, 297.1 + $fontSize, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->email, $textLeft, 425.4 + $fontSize, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->memberProfile?->residential_address ?? '-', $textLeft, 556.7 + $fontSize, $textWidth);

        ob_start();
        imagepng($canvas);
        $output = ob_get_clean();
        imagedestroy($canvas);

        return $output;
    }

    private function drawPhoto($canvas, string $binary, float $x, float $y, float $w, float $h): void
    {
        $photo = imagecreatefromstring($binary);

        if (! $photo) {
            return;
        }

        $srcW = imagesx($photo);
        $srcH = imagesy($photo);

        // Cover-fit crop, matching CSS object-fit: cover on the same box.
        $srcRatio = $srcW / $srcH;
        $dstRatio = $w / $h;

        if ($srcRatio > $dstRatio) {
            $cropH = $srcH;
            $cropW = (int) ($srcH * $dstRatio);
            $cropX = (int) (($srcW - $cropW) / 2);
            $cropY = 0;
        } else {
            $cropW = $srcW;
            $cropH = (int) ($srcW / $dstRatio);
            $cropX = 0;
            $cropY = (int) (($srcH - $cropH) / 2);
        }

        imagecopyresampled($canvas, $photo, (int) $x, (int) $y, $cropX, $cropY, (int) $w, (int) $h, $cropW, $cropH);
        imagedestroy($photo);
    }

    private function drawQr($canvas, MemberCard $memberCard, float $x, float $y, float $size): void
    {
        $png = (new Builder(data: route('verify.card', $memberCard->qr_token), size: (int) $size, margin: 0))
            ->build()
            ->getString();

        $qr = imagecreatefromstring($png);
        imagecopyresampled($canvas, $qr, (int) $x, (int) $y, 0, 0, (int) $size, (int) $size, imagesx($qr), imagesy($qr));
        imagedestroy($qr);
    }

    private function drawText($canvas, string $font, int $size, int $color, string $text, float $x, float $y, float $maxWidth): void
    {
        // Trim with an ellipsis if the text would overflow the field's line width.
        while (imagettfbbox($size, 0, $font, $text)[2] > $maxWidth && mb_strlen($text) > 1) {
            $text = mb_substr($text, 0, -1);
        }

        imagettftext($canvas, $size, 0, (int) $x, (int) $y, $color, $font, $text);
    }
}
