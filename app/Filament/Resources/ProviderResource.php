<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProviderResource\Pages;
use App\Filament\Resources\ProviderResource\RelationManagers;
use App\Models\Provider;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProviderResource extends Resource
{
    protected static ?string $model = Provider::class;

//    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationIcon = 'heroicon-s-briefcase';
//heroicon-s-briefcase
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('first_name')
                    ->required()
                    ->maxLength(15),
                Forms\Components\TextInput::make('last_name')
                    ->required()
                    ->maxLength(15),
                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(15),
                Forms\Components\Toggle::make('is_phone_verified')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->maxLength(255),
//                Forms\Components\DateTimePicker::make('email_verified_at'),
//                Forms\Components\Toggle::make('gender')
//                    ->required(),
                Forms\Components\Select::make('gender')
                    ->options([
                        true => 'Male',
                        false => 'Female',
                    ])
                    ->required(),
                Forms\Components\Select::make('country_id')
                    ->relationship('country', 'name')
                    ->required(),
                Forms\Components\Select::make('city_id')
                    ->relationship('city', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('first_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('phone')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_phone_verified')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: false), // Make visible by default
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('email_verified_at')
//                    ->dateTime()
//                    ->sortable(),
//                Tables\Columns\IconColumn::make('gender')
//                    ->boolean(),
                Tables\Columns\TextColumn::make('gender')
                    ->getStateUsing(fn($record) => self::getGenderDisplay($record->gender))
                    ->sortable(),
                Tables\Columns\TextColumn::make('country.name')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('city.name')
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
                Tables\Filters\Filter::make('gender')
                    ->form([
                        Forms\Components\Select::make('gender')
                            ->options([
                                'Male' => 'Male',
                                'Female' => 'Female',
                            ])
                            ->label('Gender'),
                    ])->query(function (Builder $query, array $data) {
                        if (isset($data['gender'])) {
                            $gender = $data['gender'] === 'Male' ? true : false;
                            $query->where('gender', $gender);
                        }
                    }),
//                Tables\Filters\Filter::make('verified')
//                    ->label('Phone Verified')
//                    ->query(function (Builder $query, array $data) {
//                        $query->where('is_phone_verified', true);
//                    })->default(array('verified' => true)),
//
//                // Filter to show records where is_phone_verified is false
//                Tables\Filters\Filter::make('not_verified')
//                    ->label('Phone Not Verified')
//                    ->query(function (Builder $query, array $data) {
//                        $query->where('is_phone_verified', false);
//                    }),
                Tables\Filters\SelectFilter::make('is_phone_verified')
                    ->options([
                        '1' => 'Verified',
                        '0' => 'Not Verified',
                    ])->default('1')
                ,
//                Tables\Filters\SelectFilter::make('phone_verified')
//                    ->label('Phone Verified')
//                    ->options([
//                        'verified' => 'Verified',
//                        'not_verified' => 'Not Verified',
//                    ])
//                    ->default('verified')
//                    ->query(function (Builder $query, $state) {
//                        if ($state === 'verified') {
//                            $query->where('is_phone_verified', true);
//                        } elseif ($state === 'not_verified') {
//                            $query->where('is_phone_verified', false);
//                        }
//                    }),
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
            'index' => Pages\ManageProviders::route('/'),
        ];
    }

    private static function getGenderDisplay(bool $gender): string
    {
        return $gender ? 'Male' : 'Female';
    }
}
