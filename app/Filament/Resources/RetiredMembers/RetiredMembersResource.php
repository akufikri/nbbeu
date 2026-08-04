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
