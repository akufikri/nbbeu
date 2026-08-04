#!/usr/bin/env bash
# Run from anywhere inside the Laravel project: bash scripts/adjust-admin-backlog-aug3.sh
# Backlog batch:
#   Admin dashboard:
#     1. Remove Union Dues admin resource entirely (member-side dues flow untouched)
#     2. Active Member edit -> Status field (Active/Retired)
#     3. member_status defaults to 'active'
#     4. New "Retired Members" admin menu
#     5. Active Members -> "Download Excel Template" (blank header row matching Import Excel)
#     6. New "Collective Agreement" textarea setting
#     7. Fix Card History download PNG sizing/typography bug
#     8. Add NBBEU logo to the certificate PDF
#   Landing page:
#     9. New public "Collective Agreement" page + nav/footer links
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
# 1) Migration — member_status column
# ------------------------------------------------------------
mkdir -p database/migrations
cat > database/migrations/2026_08_03_000001_add_member_status_to_users_table.php <<'PHP'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('member_status', ['active', 'retired'])->default('active')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('member_status');
        });
    }
};
PHP

# ------------------------------------------------------------
# 2) User model — full rewrite (adds member_status to fillable)
# ------------------------------------------------------------
mkdir -p app/Models
cat > app/Models/User.php <<'PHP'
<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable(['name', 'email', 'phone', 'company', 'password', 'photo', 'google_uid', 'member_status'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    // Filament's admin panel authenticates on a separate 'admin' session guard
    // (AdminPanelProvider), but roles are seeded/assigned under the default
    // 'web' guard. Pinning this keeps Spatie role lookups consistent
    // regardless of which guard the current request is authenticated on.
    protected string $guard_name = 'web';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'approved_at' => 'datetime',
            'renewal_expires_at' => 'date',
        ];
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function memberCards(): HasMany
    {
        return $this->hasMany(MemberCard::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function memberProfile(): HasOne
    {
        return $this->hasOne(MemberProfile::class);
    }

    public function unionDuesMandates(): HasMany
    {
        return $this->hasMany(UnionDuesMandate::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin() && $this->is_active;
    }
}
PHP

# ------------------------------------------------------------
# 3) Active Members — status field on the form
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/ActiveMembers/Schemas
cat > app/Filament/Resources/ActiveMembers/Schemas/ActiveMemberForm.php <<'PHP'
<?php

namespace App\Filament\Resources\ActiveMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class ActiveMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('name@email.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->placeholder('+60 12-345 6789')
                    ->tel()
                    ->required(),
                TextInput::make('company')
                    ->placeholder('Company / bank name')
                    ->required(),
                TextInput::make('member_no')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('member_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'retired' => 'Retired',
                    ])
                    ->default('active')
                    ->required(),
                DatePicker::make('renewal_expires_at'),
            ]);
    }
}
PHP

# ------------------------------------------------------------
# 4) Active Members resource — filter query only shows member_status=active
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/ActiveMembers
cat > app/Filament/Resources/ActiveMembers/ActiveMembersResource.php <<'PHP'
<?php

namespace App\Filament\Resources\ActiveMembers;

use App\Filament\Resources\ActiveMembers\Pages\ListActiveMembers;
use App\Filament\Resources\ActiveMembers\Pages\ViewActiveMember;
use App\Filament\Resources\ActiveMembers\Pages\EditActiveMember;
use App\Filament\Resources\ActiveMembers\Schemas\ActiveMemberForm;
use App\Filament\Resources\ActiveMembers\Tables\ActiveMembersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ActiveMembersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'active-members';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Active Members';

    protected static ?string $modelLabel = 'active member';

    protected static ?string $pluralModelLabel = 'active members';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'approved')->where('member_status', 'active');
    }

    public static function form(Schema $schema): Schema
    {
        return ActiveMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ActiveMembersTable::configure($table);
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
            'index' => ListActiveMembers::route('/'),
            'view' => ViewActiveMember::route('/{record}'),
            'edit' => EditActiveMember::route('/{record}/edit'),
        ];
    }
}
PHP

# ------------------------------------------------------------
# 5) Active Members list page — "Download Excel Template" header action
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/ActiveMembers/Pages
cat > app/Filament/Resources/ActiveMembers/Pages/ListActiveMembers.php <<'PHP'
<?php

namespace App\Filament\Resources\ActiveMembers\Pages;

use App\Exports\UsersTemplateExport;
use App\Filament\Resources\ActiveMembers\ActiveMembersResource;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class ListActiveMembers extends ListRecords
{
    protected static string $resource = ActiveMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadTemplate')
                ->label('Download Excel Template')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->color('gray')
                ->action(fn () => Excel::download(new UsersTemplateExport, 'nbbeu-members-import-template.xlsx')),
        ];
    }
}
PHP

# ------------------------------------------------------------
# 6) Blank import-template export class
# ------------------------------------------------------------
mkdir -p app/Exports
cat > app/Exports/UsersTemplateExport.php <<'PHP'
<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersTemplateExport implements FromCollection, WithHeadings
{
    public function collection(): Collection
    {
        return collect();
    }

    public function headings(): array
    {
        return ['name', 'email', 'phone', 'company'];
    }
}
PHP

# ------------------------------------------------------------
# 7) New "Retired Members" admin resource (mirrors Active Members)
# ------------------------------------------------------------
mkdir -p app/Filament/Resources/RetiredMembers/Schemas app/Filament/Resources/RetiredMembers/Tables app/Filament/Resources/RetiredMembers/Pages

cat > app/Filament/Resources/RetiredMembers/RetiredMembersResource.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers;

use App\Filament\Resources\RetiredMembers\Pages\ListRetiredMembers;
use App\Filament\Resources\RetiredMembers\Pages\ViewRetiredMember;
use App\Filament\Resources\RetiredMembers\Pages\EditRetiredMember;
use App\Filament\Resources\RetiredMembers\Schemas\RetiredMemberForm;
use App\Filament\Resources\RetiredMembers\Tables\RetiredMembersTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RetiredMembersResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'retired-members';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserMinus;

    protected static ?string $navigationLabel = 'Retired Members';

    protected static ?string $modelLabel = 'retired member';

    protected static ?string $pluralModelLabel = 'retired members';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('status', 'approved')->where('member_status', 'retired');
    }

    public static function form(Schema $schema): Schema
    {
        return RetiredMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RetiredMembersTable::configure($table);
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
            'index' => ListRetiredMembers::route('/'),
            'view' => ViewRetiredMember::route('/{record}'),
            'edit' => EditRetiredMember::route('/{record}/edit'),
        ];
    }
}
PHP

cat > app/Filament/Resources/RetiredMembers/Schemas/RetiredMemberForm.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class RetiredMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->placeholder('Full name')
                    ->required(),
                TextInput::make('email')
                    ->label('Email address')
                    ->placeholder('name@email.com')
                    ->email()
                    ->required(),
                TextInput::make('phone')
                    ->placeholder('+60 12-345 6789')
                    ->tel()
                    ->required(),
                TextInput::make('company')
                    ->placeholder('Company / bank name')
                    ->required(),
                TextInput::make('member_no')
                    ->disabled()
                    ->dehydrated(false),
                Select::make('member_status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'retired' => 'Retired',
                    ])
                    ->default('active')
                    ->required(),
                DatePicker::make('renewal_expires_at'),
            ]);
    }
}
PHP

cat > app/Filament/Resources/RetiredMembers/Tables/RetiredMembersTable.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers\Tables;

use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RetiredMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->searchable(),
                TextColumn::make('email')
                    ->label('Email address')
                    ->searchable(),
                TextColumn::make('phone'),
                TextColumn::make('company')
                    ->searchable(),
                TextColumn::make('member_no')
                    ->placeholder('-'),
                TextColumn::make('approved_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('renewal_expires_at')
                    ->date()
                    ->sortable(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ]);
    }
}
PHP

cat > app/Filament/Resources/RetiredMembers/Pages/ListRetiredMembers.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Resources\Pages\ListRecords;

class ListRetiredMembers extends ListRecords
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
PHP

cat > app/Filament/Resources/RetiredMembers/Pages/ViewRetiredMember.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewRetiredMember extends ViewRecord
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
PHP

cat > app/Filament/Resources/RetiredMembers/Pages/EditRetiredMember.php <<'PHP'
<?php

namespace App\Filament\Resources\RetiredMembers\Pages;

use App\Filament\Resources\RetiredMembers\RetiredMembersResource;
use Filament\Resources\Pages\EditRecord;

class EditRetiredMember extends EditRecord
{
    protected static string $resource = RetiredMembersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
PHP

# ------------------------------------------------------------
# 8) Remove Union Dues admin resource entirely
#    (member-side dues flow — UnionDuesController/routes/models — untouched)
# ------------------------------------------------------------
rm -rf app/Filament/Resources/UnionDues

# ------------------------------------------------------------
# 9) Site Settings — full rewrite (adds Collective Agreement tab)
# ------------------------------------------------------------
mkdir -p app/Filament/Pages
cat > app/Filament/Pages/SiteSettings.php <<'PHP'
<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class SiteSettings extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected string $view = 'filament.pages.site-settings';

    /**
     * @var array<string, mixed>
     */
    public array $data = [];

    /**
     * @var array<int, string>
     */
    protected static array $settingKeys = [
        'contact_email',
        'contact_phone',
        'contact_address',
        'social_facebook',
        'social_linkedin',
        'social_instagram',
        'footer_text',
        'how_to_join_text',
        'card_logo',
        'card_signature',
        'collective_agreement_text',
    ];

    public function mount(): void
    {
        $values = [];

        foreach (static::$settingKeys as $key) {
            $values[$key] = Setting::get($key);
        }

        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Settings')
                    ->tabs([
                        Tab::make('Contact & Social')
                            ->schema([
                                Section::make('Contact Information')
                                    ->schema([
                                        TextInput::make('contact_email')
                                            ->email()
                                            ->placeholder('info@nbbeu.org'),
                                        TextInput::make('contact_phone')
                                            ->tel()
                                            ->placeholder('+60 12-345 6789'),
                                        Textarea::make('contact_address')
                                            ->placeholder('Union office address'),
                                    ]),
                                Section::make('Social Media')
                                    ->schema([
                                        TextInput::make('social_facebook')
                                            ->url()
                                            ->placeholder('https://facebook.com/...'),
                                        TextInput::make('social_linkedin')
                                            ->url()
                                            ->placeholder('https://linkedin.com/...'),
                                        TextInput::make('social_instagram')
                                            ->url()
                                            ->placeholder('https://instagram.com/...'),
                                    ]),
                                Section::make('Footer & Join Page')
                                    ->schema([
                                        Textarea::make('footer_text')
                                            ->placeholder('Footer text shown on the public site'),
                                        Textarea::make('how_to_join_text')
                                            ->label('How to Join Text')
                                            ->rows(6)
                                            ->placeholder('Text shown in the "How to Join" section on the public home page'),
                                    ]),
                            ]),
                        Tab::make('Card & Certificate Branding')
                            ->schema([
                                FileUpload::make('card_logo')
                                    ->image()
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->directory('branding')
                                    ->helperText('Recommended size: 500x500px, square, transparent PNG. Max 2MB.'),
                                FileUpload::make('card_signature')
                                    ->image()
                                    ->maxSize(2048)
                                    ->disk('cloudinary')
                                    ->directory('branding')
                                    ->helperText('Recommended size: 400x150px, transparent PNG. Max 2MB.'),
                            ]),
                        Tab::make('Collective Agreement')
                            ->schema([
                                Textarea::make('collective_agreement_text')
                                    ->label('Collective Agreement Text')
                                    ->rows(12)
                                    ->placeholder('Text shown on the public Collective Agreement page'),
                            ]),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $values = $this->form->getState();

        foreach ($values as $key => $value) {
            Setting::set($key, $value);
        }

        Notification::make()
            ->success()
            ->title('Settings saved')
            ->send();
    }
}
PHP

# ------------------------------------------------------------
# 10) routes/web.php — full rewrite (adds collective-agreement route)
# ------------------------------------------------------------
mkdir -p routes
cat > routes/web.php <<'PHP'
<?php

use App\Http\Controllers\Member\DashboardController as MemberDashboardController;
use App\Http\Controllers\Member\DocumentController;
use App\Http\Controllers\Member\PaymentHistoryController;
use App\Http\Controllers\Member\RenewalController;
use App\Http\Controllers\Member\UnionDuesController;
use App\Http\Controllers\Membership\CardVerificationController;
use App\Http\Controllers\Membership\RegistrationStatusController;
use App\Http\Controllers\Membership\ToyyibpayWebhookController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Public\BlogController;
use App\Http\Controllers\Public\GalleryController;
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\OrgStructureController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::get('/org-structure', [OrgStructureController::class, 'index'])->name('org-structure');
Route::view('/terms', 'public.terms')->name('terms');
Route::view('/privacy', 'public.privacy')->name('privacy');
Route::get('/collective-agreement', fn () => view('public.collective-agreement', [
    'text' => \App\Models\Setting::get('collective_agreement_text'),
]))->name('collective-agreement');

Route::prefix('blog')->name('blog.')->group(function () {
    Route::get('/', [BlogController::class, 'index'])->name('index');
    Route::get('/{post}', [BlogController::class, 'show'])->name('show');
});

Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');

Route::prefix('register')->name('registration.')->group(function () {
    Route::get('/', fn () => view('membership.register-wizard'))->name('create');
    Route::get('/status', [RegistrationStatusController::class, 'show'])->name('status');
    Route::get('/return/{payment}', [ToyyibpayWebhookController::class, 'return'])->name('return');
});

Route::post('/webhooks/toyyibpay', [ToyyibpayWebhookController::class, 'callback'])
    ->name('registration.callback')
    ->middleware('throttle:60,1');

Route::get('/verify/{qrToken}', [CardVerificationController::class, 'show'])
    ->name('verify.card')
    ->middleware('throttle:60,1');

Route::get('/dashboard', [MemberDashboardController::class, 'index'])
    ->middleware(['auth:web', 'verified'])
    ->name('dashboard');

Route::middleware('auth:web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/payments', [PaymentHistoryController::class, 'index'])->name('member.payments');

    Route::prefix('member')->name('member.')->group(function () {
        Route::get('/card', [DocumentController::class, 'cardPage'])->name('card');
        Route::get('/certificate', [DocumentController::class, 'certificatePage'])->name('certificate');
        Route::get('/documents/card', [DocumentController::class, 'card'])->name('documents.card');
        Route::get('/documents/certificate', [DocumentController::class, 'certificate'])->name('documents.certificate');
        Route::get('/renewal', [RenewalController::class, 'index'])->name('renewal.index');
        Route::post('/renewal', [RenewalController::class, 'store'])->name('renewal');
        Route::get('/union-dues', [UnionDuesController::class, 'index'])->name('union-dues');
        Route::post('/union-dues', [UnionDuesController::class, 'store'])->name('union-dues.store');
        Route::get('/union-dues/{mandate}/download', [UnionDuesController::class, 'download'])->name('union-dues.download');
    });
});

require __DIR__.'/auth.php';
PHP

# ------------------------------------------------------------
# 11) Fix Card History download PNG sizing/typography bug
# ------------------------------------------------------------
mkdir -p app/Actions/Membership
cat > app/Actions/Membership/RenderMemberCardImage.php <<'PHP'
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
        // Card PDF template uses 8.5px CSS font-size on a 340x214 card; this
        // canvas is the same card scaled up to the illustration's native
        // 1594x987 resolution (~4.65x), so the font size must scale up with
        // it too — 22px was a stale guess, not derived from the PDF's actual
        // size, and rendered visibly too small for the (correctly scaled)
        // text box width/position below.
        $fontSize = (int) round(8.5 * (1594 / 340));
        // imagettftext's $y is the text baseline, not the box top — 0.8x the
        // font size approximates DejaVu Sans's baseline offset from the top
        // of its line box.
        $baselineOffset = $fontSize * 0.8;
        $this->drawText($canvas, $font, $fontSize, $navy, $user->name, $textLeft, 172.7 + $baselineOffset, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->phone, $textLeft, 297.1 + $baselineOffset, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->email, $textLeft, 425.4 + $baselineOffset, $textWidth);
        $this->drawText($canvas, $font, $fontSize, $navy, $user->memberProfile?->residential_address ?? '-', $textLeft, 556.7 + $baselineOffset, $textWidth);

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
        if (imagettfbbox($size, 0, $font, $text)[2] > $maxWidth) {
            while (mb_strlen($text) > 1 && imagettfbbox($size, 0, $font, $text.'…')[2] > $maxWidth) {
                $text = mb_substr($text, 0, -1);
            }

            $text .= '…';
        }

        imagettftext($canvas, $size, 0, (int) $x, (int) $y, $color, $font, $text);
    }
}
PHP

# ------------------------------------------------------------
# 12) Certificate PDF — add NBBEU logo
# ------------------------------------------------------------
mkdir -p resources/views/pdf
cat > resources/views/pdf/certificate.blade.php <<'BLADE'
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 0; size: A4 landscape; }
        body {
            margin: 0;
            font-family: 'DejaVu Serif', serif;
            color: #232A33;
            background-color: #F6F7F9;
        }
        .frame {
            border: 3px solid #B08D3D;
            margin: 24px;
            padding: 60px;
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
# 13) Public Collective Agreement page
# ------------------------------------------------------------
mkdir -p resources/views/public
cat > resources/views/public/collective-agreement.blade.php <<'BLADE'
<x-site-layout
    title="Collective Agreement — NBBEU"
    description="NBBEU's collective agreement with participating banking institutions."
>
    <section class="page-header">
        <div class="max-w-7xl mx-auto px-6">
            <a href="{{ route('home') }}" class="page-header__crumb">← Back to Home</a>
            <h1>Collective Agreement</h1>
        </div>
    </section>

    <section class="py-16">
        <div class="article-body clause mx-auto px-6">
            @if ($text)
                <div class="clause__item">
                    <p>{!! nl2br(e($text)) !!}</p>
                </div>
            @else
                <p class="text-nb-ink-muted font-sans text-sm">Collective agreement details will be published here soon.</p>
            @endif
        </div>
    </section>
</x-site-layout>
BLADE

# ------------------------------------------------------------
# 14) Site layout — full rewrite (nav/mobile/footer "Perjanjian Kolektif" link)
# ------------------------------------------------------------
mkdir -p resources/views/components
cat > resources/views/components/site-layout.blade.php <<'BLADE'
@props([
    'title' => 'NBBEU — North Borneo Banking Executive Union',
    'description' => "NBBEU upholds the highest professional standards, facilitates strategic dialogue, and protects the collective interests of banking industry leaders across North Borneo.",
    'hideNav' => false,
])
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <meta name="description" content="{{ $description }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logo.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@500&family=IBM+Plex+Sans:wght@400;500&family=Spectral:ital,wght@0,400;0,600;1,400&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'nb-primary': 'oklch(31.5% 0.085 260.2)',
                        'nb-primary-active': 'oklch(26.3% 0.068 259.1)',
                        'nb-paper': 'oklch(97.6% 0.003 264.5)',
                        'nb-paper-raised': 'oklch(100% 0 0)',
                        'nb-paper-final': 'oklch(93.9% 0.006 255.5)',
                        'nb-ink': 'oklch(28.2% 0.019 254.8)',
                        'nb-ink-muted': 'oklch(55.1% 0.023 264.4)',
                        'nb-rule': 'oklch(89.3% 0.011 261.8)',
                        'nb-accent': 'oklch(69.0% 0.115 206.7)',
                        'sig-navy': 'oklch(23.4% 0.061 259.5)',
                        'sig-teal': 'oklch(41.4% 0.067 210.9)',
                        'sig-cream': 'oklch(94.3% 0.025 83.4)',
                    },
                    fontFamily: {
                        sans: ['"IBM Plex Sans"', 'sans-serif'],
                        serif: ['Spectral', 'serif'],
                        mono: ['"IBM Plex Mono"', 'monospace'],
                    },
                }
            }
        }
    </script>
    <link rel="stylesheet" href="{{ asset('assets/site/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/site/style.css') }}">
</head>
<body class="bg-nb-paper font-sans text-nb-ink antialiased selection:bg-nb-accent/20">

    @unless ($hideNav)
    <header id="navbar" class="masthead sticky top-0 z-50">
        <div class="masthead-top">
            <div class="masthead-top__inner">
                <p class="masthead-kicker">Ditubuhkan 2024 · North Borneo Banking Executive Union</p>
                <div class="masthead-wordmark-row">
                    <a href="{{ route('home') }}" class="masthead-wordmark-link">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="" class="masthead-logo">
                        <span class="masthead-wordmark">NBBEU</span>
                    </a>
                </div>
                <div class="masthead-rule masthead-rule--double"></div>
            </div>
        </div>
        <div class="max-w-7xl mx-auto px-6">
            <div class="masthead-nav-row">
                <button id="mobile-menu-toggle" class="masthead-toggle lg:hidden text-nb-primary" aria-label="Toggle Menu">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <nav class="masthead-links hidden lg:flex">
                    <a href="{{ route('home') }}" class="masthead-compact-logo-link">
                        <img src="{{ asset('assets/images/logo.png') }}" alt="NBBEU" class="masthead-compact-logo">
                    </a>
                    <a href="{{ route('home') }}#tentang-kami">Tentang Kami</a>
                    <a href="{{ route('home') }}#program">Program</a>
                    <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                    <a href="{{ route('blog.index') }}">Berita</a>
                    <a href="{{ route('gallery.index') }}">Galeri</a>
                    <a href="{{ route('collective-agreement') }}">Perjanjian Kolektif</a>
                    <a href="{{ route('home') }}#contact">Hubungi Kami</a>
                </nav>
                <div class="masthead-actions hidden lg:flex">
                    @auth
                        <div class="user-menu">
                            <button type="button" id="user-menu-toggle" class="user-menu__trigger" aria-haspopup="true" aria-expanded="false">
                                {{ auth()->user()->name }}
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                            </button>
                            <div id="user-menu-dropdown" class="user-menu__dropdown hidden">
                                <a href="{{ route('dashboard') }}">Dashboard</a>
                                <a href="{{ route('profile.edit') }}">Profil</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit">Log Keluar</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" id="login-link">Log Masuk Ahli</a>
                        <a href="{{ route('registration.create') }}" id="nav-join-btn" class="btn-primary cta-fill">Sertai Keahlian</a>
                    @endauth
                </div>
            </div>
        </div>

        <div id="mobile-menu" class="hidden lg:hidden bg-nb-paper">
            <div class="px-6 py-4 flex flex-col">
                <a href="{{ route('home') }}#tentang-kami" class="mobile-link">Tentang Kami</a>
                <a href="{{ route('home') }}#program" class="mobile-link">Program</a>
                <a href="{{ route('org-structure') }}" class="mobile-link">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}" class="mobile-link">Berita</a>
                <a href="{{ route('gallery.index') }}" class="mobile-link">Galeri</a>
                <a href="{{ route('collective-agreement') }}" class="mobile-link">Perjanjian Kolektif</a>
                <a href="{{ route('home') }}#contact" class="mobile-link">Hubungi Kami</a>
                @auth
                    <a href="{{ route('dashboard') }}" class="mobile-link">Dashboard</a>
                    <a href="{{ route('profile.edit') }}" class="mobile-link">Profil</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-primary cta-fill w-full mt-3 justify-center">Log Keluar</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="mobile-link">Log Masuk Ahli</a>
                    <a href="{{ route('registration.create') }}" class="btn-primary cta-fill w-full mt-3 justify-center">Sertai Keahlian</a>
                @endauth
            </div>
        </div>
    </header>
    @endunless

    {{ $slot }}

    <footer id="contact" class="bg-sig-navy text-white/80 py-16">
        <div class="max-w-7xl mx-auto px-6 colophon">
            <p class="text-white"><strong>NORTH BORNEO BANKING EXECUTIVE UNION (NBBEU)</strong></p>
            <p class="mt-6">
                Lot 1, Block A, Jalan Ikan Juara 1, Tingkat 2, Sadong Jaya, 88818 Kota Kinabalu, Sabah.<br>
                E-mel: nbbeusabah@gmail.com · Telefon: +60165830034<br>
                Waktu Pejabat: Isnin - Jumaat, 9.00 pagi - 5.00 petang
            </p>
            <p class="mt-6 colophon__links">
                <a href="{{ route('home') }}#tentang-kami">Tentang Kami</a>
                <a href="{{ route('home') }}#program">Program</a>
                <a href="{{ route('org-structure') }}">Struktur Organisasi</a>
                <a href="{{ route('blog.index') }}">Berita</a>
                <a href="{{ route('gallery.index') }}">Galeri</a>
                <a href="{{ route('collective-agreement') }}">Perjanjian Kolektif</a>
                <a href="{{ route('privacy') }}">Dasar Privasi</a>
                <a href="{{ route('terms') }}">Terma &amp; Syarat</a>
            </p>
            <p class="mt-8 text-white/50">© {{ now()->year }} North Borneo Banking Executive Union (NBBEU). Hak Cipta Terpelihara.</p>
        </div>
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="{{ asset('assets/site/script.js') }}"></script>
    @stack('scripts')
</body>
</html>
BLADE

# ------------------------------------------------------------
# 15) Migrate + clear caches
# ------------------------------------------------------------
php artisan migrate --force
php artisan optimize:clear

echo "Done: 9-item admin/landing backlog applied (Union Dues removed, member_status/Retired Members added,"
echo "Excel template, Collective Agreement setting + public page, Card History PNG fixed, Certificate logo added)."
