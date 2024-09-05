<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvoiceResource\Pages;
use App\Filament\Resources\InvoiceResource\RelationManagers;
use App\Models\Invoice;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoiceResource extends Resource
{
    protected static ?string $model = Invoice::class;

    protected static ?string $navigationIcon = 'heroicon-m-document-currency-dollar';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
//                Forms\Components\TextInput::make('uuid')
//                    ->label('UUID')
//                    ->required()
//                    ->maxLength(36),
//                Forms\Components\TextInput::make('invoice_number')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('status')
//                    ->required(),
//                Forms\Components\TextInput::make('cost')
//                    ->numeric()
//                    ->prefix('$'),
                Forms\Components\TextInput::make('discount')
                    ->numeric(),
                Forms\Components\TextInput::make('tax')
                    ->numeric(),
                Forms\Components\TextInput::make('sub_total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('total')
                    ->required()
                    ->numeric(),
                Forms\Components\TextInput::make('provider_earning')
                    ->numeric(),
                Forms\Components\TextInput::make('admin_earning')
                    ->numeric(),
//                Forms\Components\TextInput::make('details')
//                    ->maxLength(255),
                Forms\Components\TextInput::make('payment_method')
                    ->required(),
                Forms\Components\TextInput::make('payment_status')
                    ->required()
                    ->maxLength(255)
                    ->default('pending'),
//                Forms\Components\TextInput::make('payment_id')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('payment_url')
//                    ->maxLength(255),
//                Forms\Components\TextInput::make('user_id')
//                    ->numeric(),
                Forms\Components\TextInput::make('order_id')
                    ->numeric(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('uuid')
                    ->label('invoice number')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('invoice_number')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('status'),
//                Tables\Columns\TextColumn::make('cost')
//                    ->money()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('discount')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tax')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('sub_total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('provider_earning')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('admin_earning')
                    ->numeric()
                    ->sortable(),
//                Tables\Columns\TextColumn::make('details')
//                    ->searchable(),
                Tables\Columns\TextColumn::make('payment_method'),
                Tables\Columns\TextColumn::make('payment_status')
                    ->searchable(),
//                Tables\Columns\TextColumn::make('payment_id')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('payment_url')
//                    ->searchable(),
//                Tables\Columns\TextColumn::make('user_id')
//                    ->numeric()
//                    ->sortable(),
                Tables\Columns\TextColumn::make('order_id')
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
//                Tables\Actions\EditAction::make(),
//                Tables\Actions\EditAction::make()->after(function () {
//
//                })->visible(fn($record): bool => $record->payment_status !== 'paid'),
//                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageInvoices::route('/'),
        ];
    }
}
