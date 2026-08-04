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
