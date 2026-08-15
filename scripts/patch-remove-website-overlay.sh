#!/usr/bin/env bash
# Patch: remove www.nbbeu.org.my text overlay from member card
# (website already printed on card template image — duplicate removed)
set -euo pipefail

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
APP_ROOT="$SCRIPT_DIR"
while [ ! -f "$APP_ROOT/artisan" ] && [ "$APP_ROOT" != "/" ]; do
    APP_ROOT="$(dirname "$APP_ROOT")"
done
[ -f "$APP_ROOT/artisan" ] || { echo "artisan not found"; exit 1; }
cd "$APP_ROOT"

# 1) RenderMemberCardImage.php — remove $dbrown + website drawCentered
php -r "
\$f = 'app/Actions/Membership/RenderMemberCardImage.php';
\$c = file_get_contents(\$f);
\$c = preg_replace('/\s*\\\$dbrown\s*=\s*imagecolorallocate[^\n]+\n/', '', \$c);
\$c = preg_replace('/\s*\\\$this->drawCentered\([^)]+www\.nbbeu\.org\.my[^)]+\);\n/', '', \$c);
file_put_contents(\$f, \$c);
echo 'patched: ' . \$f . PHP_EOL;
"

# 2) pdf/member-card.blade.php — remove .f-web CSS + f-web div
php -r "
\$f = 'resources/views/pdf/member-card.blade.php';
\$c = file_get_contents(\$f);
\$c = preg_replace('/\s*\.f-web\s*\{[^}]+\}\n/', '', \$c);
\$c = preg_replace('/<div class=\"field f-web\">www\.nbbeu\.org\.my<\/div>\n/', '', \$c);
file_put_contents(\$f, \$c);
echo 'patched: ' . \$f . PHP_EOL;
"

# 3) member/card.blade.php — remove web preview www paragraph
php -r "
\$f = 'resources/views/member/card.blade.php';
\$c = file_get_contents(\$f);
\$c = preg_replace('/<p[^>]*color:#5C4000[^>]*>\s*www\.nbbeu\.org\.my\s*<\/p>/', '', \$c);
file_put_contents(\$f, \$c);
echo 'patched: ' . \$f . PHP_EOL;
"

# 4) Clear cache
php artisan optimize:clear

# 5) Regenerate cards (new design without website overlay)
echo "Regenerating member cards..."
php artisan tinker --execute="
\$action = app(App\Actions\Membership\GenerateMemberCard::class);
\$users = App\Models\User::where('status', 'approved')->whereNotNull('member_no')->get();
echo 'Total: '.\$users->count().PHP_EOL;
foreach (\$users as \$u) {
    try { \$action(\$u); echo '  OK: '.\$u->member_no.PHP_EOL; }
    catch (\Throwable \$e) { echo '  FAIL: '.\$u->member_no.' — '.\$e->getMessage().PHP_EOL; }
}
"

echo "Done."
