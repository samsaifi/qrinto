<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('order_print_logs', function (Blueprint $table) {
            $table->id();

            // Identity
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_item_id')->nullable()->constrained('order_items')->nullOnDelete();
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number', 64)->nullable();
            $table->timestamp('printed_at');

            // Printer target
            $table->string('printer_name', 160);
            $table->string('tray_key', 120)->nullable();
            $table->string('tray_label', 160)->nullable();
            $table->boolean('is_default_printer')->default(false);
            $table->unsignedSmallInteger('copies')->default(1);

            // Media snapshot (from the tray config at print time)
            $table->string('size', 20)->nullable();
            $table->string('media', 40)->nullable();
            $table->string('gsm', 20)->nullable();
            $table->unsignedTinyInteger('user_type')->nullable();

            // File snapshot
            $table->string('pdf_path', 255)->nullable();
            $table->unsignedBigInteger('pdf_bytes')->nullable();
            $table->char('pdf_sha256', 64)->nullable();

            // Outcome
            $table->enum('status', ['success', 'failed', 'retried'])->default('success');
            $table->text('error_message')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            // Client context
            $table->string('client_ip', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('qz_tray_version', 32)->nullable();

            $table->timestamps();

            $table->index(['store_id', 'printed_at']);
            $table->index(['order_id']);
            $table->index(['printer_name', 'printed_at']);
            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_print_logs');
    }
};
