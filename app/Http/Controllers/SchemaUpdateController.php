<?php

namespace App\Http\Controllers;

use App\DataTables\UserDataTable;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SchemaUpdateController extends Controller
{
    public function users(UserDataTable $dataTable){
        return $dataTable->render('users');
    }
    public function updateSchema()
    {
//        Schema::table('payments', static function (Blueprint $table) {
//            $table->boolean('is_reviewed')->default(true);
//
//            // Foreign key to the admins table for creator_id
//            $table->foreignId('creator_id')->constrained('admins')->onDelete('cascade');
//
//            // Foreign key to the admins table for reviewer_id
//            $table->foreignId('reviewer_id')->constrained('admins')->onDelete('cascade');
//        });
        Schema::table('invoices', static function (Blueprint $table) {
            $table->boolean('is_sent')->default(false)->after('order_id');
        });
        Schema::table('levels', static function (Blueprint $table) {
            $table->unsignedInteger('percentage')->after('level');
        });

//        Schema::table('tickets', static function (Blueprint $table) {
//            $table->enum('status', ['open', 'pending', 'closed'])->default('open')->change();
//        });
        Schema::table('orders', static function (Blueprint $table) {
//            $table->foreignId('location_id') // Column name should be explicit (location_id)
//            ->after('unknown_problem')// Only if you want to specify order
//            ->nullable()               // Nullable constraint
//            ->constrained('locations') // Explicitly mention the related table (optional, Laravel will infer it)
//            ->nullOnDelete()   ;        // Set to null on delete of the related record
            $table->string('location_name')->after('unknown_problem')->nullable();
        });
        Schema::table('reviews', static function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('order_id');
        });
        Schema::table('reviews', static function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('order_id');
        });
        Schema::table('orders', static function (Blueprint $table) {
            $table->boolean('is_confirmed')->default(0)->after('status');
        });
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
