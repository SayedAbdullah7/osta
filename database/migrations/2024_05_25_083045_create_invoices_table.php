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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->uuid();
            $table->string('invoice_number')->unique()->nullable();
            $table->string('status')->default('pending');
            $table->decimal('cost', 10, 2)->nullable();
            $table->decimal('discount', 10, 2)->nullable();
            $table->decimal('tax', 10, 2)->nullable();

            $table->decimal('sub_total', 10, 2);
            $table->decimal('total', 10, 2);

            $table->decimal('provider_earning', 10, 2)->nullable();
            $table->decimal('admin_earning', 10, 2)->nullable();

            $table->enum('payment_method',['cash','wallet','card']);
            $table->string('payment_status')->default('pending');
            $table->string('payment_id')->nullable();
            $table->string('payment_url')->nullable();


            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('cascade');


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
