#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-leadership-titles.sh
# Fixes 2 leadership title labels: "Bendahari" -> "Bendahari Agung",
# "Timbalan Bendahari" -> "Timbalan Bendahari Agung".
# Updates org_chart rows directly (targeted, does NOT delete/reseed the
# table) so any photos already uploaded via the admin panel are preserved.
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

php artisan tinker --execute="
App\Models\OrgChart::where('name', 'Emmanuel Sylvester')->update(['position' => 'Bendahari Agung']);
App\Models\OrgChart::where('name', 'Zahaibuddin Bin Cappi')->update(['position' => 'Timbalan Bendahari Agung']);
App\Models\OrgChart::orderBy('display_order')->get(['name','position'])->each(fn(\$o) => print(\$o->name.' - '.\$o->position.PHP_EOL));
"

# ------------------------------------------------------------
# Keep LeadershipSeeder.php in sync so a future reseed matches too
# ------------------------------------------------------------
SEEDER="database/seeders/LeadershipSeeder.php"
if [ -f "$SEEDER" ]; then
    perl -0777 -pi -e "s/'position' => 'Bendahari'/'position' => 'Bendahari Agung'/; s/'position' => 'Timbalan Bendahari'/'position' => 'Timbalan Bendahari Agung'/" "$SEEDER"
    echo "Patched: $SEEDER"
fi

php artisan optimize:clear

echo "Done: Bendahari -> Bendahari Agung, Timbalan Bendahari -> Timbalan Bendahari Agung."
