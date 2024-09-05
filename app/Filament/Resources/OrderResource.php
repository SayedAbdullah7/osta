<?php

namespace App\Filament\Resources;

use App\Enums\OrderCategoryEnum;
use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Filament\Resources\OrderResource\Widgets\StatsOverview;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\DateTimePicker::make('start'),
//                Forms\Components\DateTimePicker::make('end'),
                Forms\Components\TextInput::make('space')
                    ->maxLength(15),
//                Forms\Components\TextInput::make('warranty_id'),
                Forms\Components\Select::make('status')
                    ->options(OrderStatusEnum::array())
                    ->required(),
                Forms\Components\Select::make('category')
                    ->options(OrderCategoryEnum::array())
                    ->required(),
                Forms\Components\TextInput::make('desc')
                    ->maxLength(255),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->prefix('$'),
                Forms\Components\TextInput::make('max_allowed_price')
                    ->numeric(),
                Forms\Components\TextInput::make('location_latitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('location_longitude')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('location_desc')
                    ->maxLength(255),
                Forms\Components\Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                Forms\Components\Select::make('service_id')
                    ->relationship('service', 'name')
                    ->required(),
                Forms\Components\Select::make('provider_id')
                    ->relationship('provider', 'first_name')
                    ->nullable(),
                Forms\Components\Toggle::make('unknown_problem')
                    ->required(),
                Map::make('location')->visibleOn('view')

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id'),
//                Tables\Columns\TextColumn::make('start')
//                    ->dateTime()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('end')
//                    ->dateTime()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('space')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('warranty_id'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('category'),
                Tables\Columns\TextColumn::make('desc')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->money()
                    ->sortable(),
                Tables\Columns\TextColumn::make('max_allowed_price')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('unknown_problem')
                    ->boolean(),
//                Tables\Columns\TextColumn::make('location_latitude')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('location_longitude')
//                    ->numeric()
//                    ->sortable(),
//                Tables\Columns\TextColumn::make('location_desc')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('service.name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('provider.name')
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()->visible(fn($record): bool => $record->status  !=  OrderStatusEnum::DONE),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('id', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\SubServicesRelationManager::class,
            RelationManagers\OffersRelationManager::class,
        ];
    }

    public static function getWidgets(): array
    {
        return [
            StatsOverview::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }


}
