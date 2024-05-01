
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start')->nullable();
            $table->dateTime('end')->nullable();
            $table->string('space',15)->nullable();
            $table->enum('warranty_id', \App\Enums\OrderWarrantyEnum::values())->nullable();
            $table->enum('status',  \App\Enums\OrderStatusEnum::values())->default(\App\Enums\OrderStatusEnum::PENDING->value);
//            $table->enum('status', [\App\Enums\OrderStatusEnum::PENDING, \App\Enums\OrderStatusEnum::ACCEPTED, \App\Enums\OrderStatusEnum::COMING, \App\Enums\OrderStatusEnum::ALMOST_DONE, \App\Enums\OrderStatusEnum::DONE])->default(\App\Enums\OrderStatusEnum::PENDING);
            $table->enum('category', \App\Enums\OrderCategoryEnum::values());
            $table->string('desc')->nullable();// change to VARCHAR
            $table->mediumInteger('price')->unsigned()->nullable();
            $table->mediumInteger('max_allowed_price')->unsigned()->nullable();
            $table->boolean('unknown_problem')->default(false);
            $table->foreignIdFor(\App\Models\User::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Service::class)->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Provider::class)->nullable()->constrained()->restrictOnDelete();
            $table->foreignIdFor(\App\Models\Location::class)->constrained()->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
