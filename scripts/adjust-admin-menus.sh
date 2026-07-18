#!/usr/bin/env bash
# Run from Laravel project root: bash scripts/adjust-admin-menus.sh
# 1) removes "Renewal Status" admin menu
# 2) splits "Document History" into "Card History" + "Certificate History"
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
RES_DIR="$APP_ROOT/app/Filament/Resources"

# ------------------------------------------------------------
# 1) Remove Renewal Status resource
# ------------------------------------------------------------
rm -rf "$RES_DIR/RenewalStatus"

# ------------------------------------------------------------
# 2) Remove old combined Document History resource (if present)
# ------------------------------------------------------------
rm -rf "$RES_DIR/DocumentHistory"

# ------------------------------------------------------------
# 3) Card History resource
# ------------------------------------------------------------
mkdir -p "$RES_DIR/CardHistory/Pages" "$RES_DIR/CardHistory/Tables"

cat > "$RES_DIR/CardHistory/CardHistoryResource.php" <<'PHP'
<?php

namespace App\Filament\Resources\CardHistory;

use App\Filament\Resources\CardHistory\Pages\ListCardHistory;
use App\Filament\Resources\CardHistory\Tables\CardHistoryTable;
use App\Models\MemberCard;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CardHistoryResource extends Resource
{
    protected static ?string $model = MemberCard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $navigationLabel = 'Card History';

    protected static ?string $slug = 'card-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Card & Certificate';

    public static function table(Table $table): Table
    {
        return CardHistoryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCardHistory::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
PHP

cat > "$RES_DIR/CardHistory/Pages/ListCardHistory.php" <<'PHP'
<?php

namespace App\Filament\Resources\CardHistory\Pages;

use App\Filament\Resources\CardHistory\CardHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCardHistory extends ListRecords
{
    protected static string $resource = CardHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
PHP

cat > "$RES_DIR/CardHistory/Tables/CardHistoryTable.php" <<'PHP'
<?php

namespace App\Filament\Resources\CardHistory\Tables;

use App\Actions\Membership\GenerateMemberCard;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

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
                Action::make('download_card')
                    ->label('Download Card')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(fn ($record) => response()->download(Storage::path($record->file_path), basename($record->file_path)))
                    ->visible(fn ($record) => filled($record->file_path) && Storage::exists($record->file_path)),
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

# ------------------------------------------------------------
# 4) Certificate History resource
# ------------------------------------------------------------
mkdir -p "$RES_DIR/CertificateHistory/Pages" "$RES_DIR/CertificateHistory/Tables"

cat > "$RES_DIR/CertificateHistory/CertificateHistoryResource.php" <<'PHP'
<?php

namespace App\Filament\Resources\CertificateHistory;

use App\Filament\Resources\CertificateHistory\Pages\ListCertificateHistory;
use App\Filament\Resources\CertificateHistory\Tables\CertificateHistoryTable;
use App\Models\Certificate;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CertificateHistoryResource extends Resource
{
    protected static ?string $model = Certificate::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Certificate History';

    protected static ?string $slug = 'certificate-history';

    protected static string|\UnitEnum|null $navigationGroup = 'Card & Certificate';

    public static function table(Table $table): Table
    {
        return CertificateHistoryTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCertificateHistory::route('/'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
PHP

cat > "$RES_DIR/CertificateHistory/Pages/ListCertificateHistory.php" <<'PHP'
<?php

namespace App\Filament\Resources\CertificateHistory\Pages;

use App\Filament\Resources\CertificateHistory\CertificateHistoryResource;
use Filament\Resources\Pages\ListRecords;

class ListCertificateHistory extends ListRecords
{
    protected static string $resource = CertificateHistoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
PHP

cat > "$RES_DIR/CertificateHistory/Tables/CertificateHistoryTable.php" <<'PHP'
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

# ------------------------------------------------------------
# 5) Clear caches so Filament re-discovers the new resources
# ------------------------------------------------------------
cd "$APP_ROOT"
php artisan optimize:clear

echo "Done: Renewal Status removed; Document History split into Card History + Certificate History."
