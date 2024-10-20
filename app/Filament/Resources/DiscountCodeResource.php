<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DiscountCodeResource\Pages;
use App\Filament\Resources\DiscountCodeResource\RelationManagers;
use App\Models\DiscountCode;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class DiscountCodeResource extends Resource
{
    protected static ?string $model = DiscountCode::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('code')
                    ->required()
                    ->maxLength(255)
                    ->default(fn () => self::generateUniqueCode())
                    ->unique(DiscountCode::class, 'code', fn ($record) => $record),
                Forms\Components\TextInput::make('value')
                    ->minValue(0.01)
                    ->required()
                    ->numeric(),
                Forms\Components\Select::make('type')
                    ->required()
                    ->options([
                        'fixed' => 'Fixed Amount',
                        'percentage' => 'Percentage',
                    ]),
                Forms\Components\Toggle::make('is_active')
                    ->label('Is Active')
                    ->default(true),
                Forms\Components\DatePicker::make('expires_at')->nullable(),
                Forms\Components\Select::make('used_by')
                    ->relationship('user', 'name')
                    ->label('Used By')
                    ->hidden(fn ($record) => !$record),
                Forms\Components\DateTimePicker::make('used_at')->hidden(fn ($record) => !$record),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable(),
                Tables\Columns\TextColumn::make('value')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('type'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('used_by')
                    ->numeric()
                    ->sortable(),
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
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageDiscountCodes::route('/'),
        ];
    }

    private static function generateUniqueCode(): string
    {
        do {
            $code = 'CODE-' . strtoupper(Str::random(6)); // Shorter random code
           } while (DiscountCode::where('code', $code)->exists());

        return $code;
    }
}
