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
