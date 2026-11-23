<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('service_type')->default('partial');
        });

        Schema::table('laundry_orders', function (Blueprint $table): void {
            $table->string('service_type')->default('partial');
            $table->decimal('full_service_addon_per_kg', 8, 2)->default(0);
        });

        DB::table('laundry_orders')->update(['service_type' => 'full']);

        Schema::table('laundry_settings', function (Blueprint $table): void {
            $table->decimal('full_service_addon_per_kg', 8, 2)->default(15);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laundry_settings', function (Blueprint $table): void {
            $table->dropColumn('full_service_addon_per_kg');
        });

        Schema::table('laundry_orders', function (Blueprint $table): void {
            $table->dropColumn(['service_type', 'full_service_addon_per_kg']);
        });

        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropColumn('service_type');
        });
    }
};
