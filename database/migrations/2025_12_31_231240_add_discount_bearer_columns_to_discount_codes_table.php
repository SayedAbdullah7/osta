<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Add discount bearer and warranty application columns.
 *
 * COLUMNS ADDED:
 * ==============
 * 1. bearer (enum): Who bears the discount cost
 *    - 'admin': Admin only bears the discount (provider earnings unaffected)
 *    - 'both': Both admin and provider share the discount
 *    - Default: 'both'
 *
 * 2. apply_to_warranty (boolean): Whether discount applies to warranty cost
 *    - true: Discount is calculated on (offer_price + additional_cost + warranty)
 *    - false: Discount is calculated on (offer_price + additional_cost) only
 *    - Default: false (warranty not discounted)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            // Who bears the discount: 'admin' = admin only, 'both' = admin and provider
            $table->enum('bearer', ['admin', 'both'])->default('both')->after('type');

            // Whether discount applies to warranty cost
            $table->boolean('apply_to_warranty')->default(false)->after('bearer');
        });
    }

    public function down(): void
    {
        Schema::table('discount_codes', function (Blueprint $table) {
            $table->dropColumn(['bearer', 'apply_to_warranty']);
        });
    }
};
