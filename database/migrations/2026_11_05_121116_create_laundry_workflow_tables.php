<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->dateTime('scheduled_at');
            $table->decimal('estimated_weight', 8, 2);
            $table->boolean('press_service')->default(false);
            $table->string('status')->default('pending')->index();
            $table->text('customer_note')->nullable();
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('laundry_orders', function (Blueprint $table): void {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('customer_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('appointment_id')->nullable()->unique()->constrained()->nullOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->decimal('weight', 8, 2);
            $table->boolean('press_service')->default(false);
            $table->decimal('wash_price_per_kg', 8, 2);
            $table->decimal('press_price_per_kg', 8, 2)->default(35);
            $table->decimal('total_amount', 10, 2);
            $table->string('status')->default('received')->index();
            $table->dateTime('due_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('payments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('laundry_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('received_by')->constrained('users')->restrictOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('method');
            $table->string('reference')->nullable();
            $table->dateTime('paid_at');
            $table->timestamps();
        });

        Schema::create('laundry_settings', function (Blueprint $table): void {
            $table->id();
            $table->decimal('wash_price_per_kg', 8, 2)->default(26);
            $table->decimal('press_price_per_kg', 8, 2)->default(35);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('sms_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('laundry_order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('recipient', 32);
            $table->text('message');
            $table->string('status')->default('pending')->index();
            $table->string('provider_reference')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        Schema::create('activity_notifications', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('url')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_notifications');
        Schema::dropIfExists('sms_messages');
        Schema::dropIfExists('laundry_settings');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('laundry_orders');
        Schema::dropIfExists('appointments');
    }
};
