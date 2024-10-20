<?php

namespace App\Http\Controllers;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class SchemaUpdateController extends Controller
{
    public function updateSchema()
    {
        Schema::table('orders', static function (Blueprint $table) {
            $table->boolean('is_confirmed')->default(0)->after('status');
        });
//        return 'a';
        Schema::table('orders', static function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'max_price')) {
                return 'done1';
                $table->mediumInteger('max_price')->unsigned()->nullable();
            }
        });
        Schema::table('order_sub_service', static function (Blueprint $table) {
//            return 'a';
            if (!Schema::hasColumn('order_sub_service', 'max_price')) {
                $table->mediumInteger('max_price')->unsigned()->nullable();
                return 'done1';
            }
        });
        Schema::table('messages', static function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'order_id')) {
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
                return 'done';
            }
        });
        return 'passed';
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'discount_code')) {
                $table->string('discount_code')->nullable()->after('location_desc');
            }
            if (!Schema::hasColumn('orders', 'offer_count')) {
                $table->tinyInteger('offer_count')->unsigned()->default(0)->after('discount_code');
            }
        });
        Schema::table('provider_review_statistics', function (Blueprint $table) {
            if (!Schema::hasColumn('provider_review_statistics', 'completed_orders')) {
                $table->integer('completed_orders')->unsigned()->default(0)->after('average_rating');
            }
        });
        Schema::table('order_sub_service', static function (Blueprint $table) {
            if (!Schema::hasColumn('order_sub_service', 'space_name')) {
                $table->string('space_name')->nullable()->after('quantity');
            }
        });
        Schema::table('messages', static function (Blueprint $table) {
            if (!Schema::hasColumn('messages', 'order_id')) {
                $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            }
        });

        // Check if the columns exist before adding them
        Schema::table('users', function (Blueprint $table) {
//            if (!Schema::hasColumn('users', 'new_column_1')) {
//                $table->string('new_column_1')->nullable();
//            }
//
//            if (!Schema::hasColumn('users', 'new_column_2')) {
//                $table->integer('new_column_2')->default(0);
//            }
            if (Schema::hasColumn('users', 'new_column_2')) {
                $table->dropColumn('new_column_2');
            }
            if (Schema::hasColumn('users', 'new_column_1')) {
                $table->dropColumn('new_column_1');
            }
            if (Schema::hasColumn('users', 'messenger_color')) {
                $table->dropColumn('messenger_color');
            }
            if (Schema::hasColumn('users', 'dark_mode')) {
                $table->dropColumn('dark_mode');
            }
            if (Schema::hasColumn('users', 'avatar')) {
                $table->dropColumn('avatar');
            }
            if (Schema::hasColumn('users', 'active_status')) {
                $table->dropColumn('active_status');
            }
        });

        return response()->json(['message' => 'Schema updated successfully']);
    }
}
