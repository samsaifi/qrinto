<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Audit trail for prints initiated from the local-print flow:
 *   - /print/pdf/check                         (source = pdf_check)
 *   - /print/own-design/sizes/customizer/preview (source = own_design_customizer_preview)
 *
 * One row per print ATTEMPT, created before the PrintTrays request is sent and
 * updated in place with the outcome. The complete PrintTrays payload/response
 * is preserved as JSON so an admin can reproduce or audit exactly what was sent.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('local_print_logs', function (Blueprint $table) {
            $table->id();

            // ── Who / where ──
            $table->foreignId('store_id')->nullable()->constrained('stores')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // ── Source page ──
            $table->string('source', 60)->nullable();        // pdf_check | own_design_customizer_preview | local
            $table->string('source_url', 255)->nullable();
            $table->string('print_type', 40)->nullable();    // pdf | image
            $table->string('status', 20)->default('pending'); // pending|sending|sent|printing|completed|failed|cancelled

            // ── Design / order linkage (nullable) ──
            $table->unsignedBigInteger('order_id')->nullable();
            $table->unsignedBigInteger('design_id')->nullable();
            $table->unsignedBigInteger('template_id')->nullable();
            $table->unsignedBigInteger('product_id')->nullable();

            // ── Printer snapshot ──
            $table->string('printer_name', 160)->nullable();
            $table->string('printer_id', 160)->nullable();
            $table->string('printer_model', 160)->nullable();
            $table->string('tray_key', 120)->nullable();
            $table->string('tray_label', 160)->nullable();
            $table->string('paper_source', 120)->nullable();  // input bin
            $table->string('paper_size', 120)->nullable();
            $table->string('media_size', 120)->nullable();
            $table->string('orientation', 20)->nullable();
            $table->string('color_mode', 20)->nullable();
            $table->string('duplex', 30)->nullable();
            $table->string('scale_mode', 20)->nullable();
            $table->unsignedInteger('scale_percentage')->nullable();
            $table->unsignedSmallInteger('copies')->default(1);

            // ── File snapshot ──
            $table->string('file_url', 512)->nullable();
            $table->string('file_name', 255)->nullable();
            $table->string('file_type', 40)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->unsignedInteger('page_count')->nullable();
            $table->string('page_dimensions', 60)->nullable(); // e.g. "7 x 5 in"

            // ── Complete payloads (JSON) ──
            $table->json('request_payload')->nullable();   // exact PrintTrays request/config
            $table->json('response_payload')->nullable();  // PrintTrays response/error
            $table->json('printer_settings')->nullable();  // full printer config snapshot
            $table->json('design_settings')->nullable();   // design/customizer/size snapshot
            $table->json('system_information')->nullable(); // client/system context

            // ── Job reference / error ──
            $table->string('printtrays_job_id', 120)->nullable();
            $table->text('error_message')->nullable();

            // ── Client context ──
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->string('browser', 120)->nullable();
            $table->string('operating_system', 120)->nullable();
            $table->string('device_name', 160)->nullable();
            $table->string('session_id', 100)->nullable();

            // ── Timing ──
            $table->timestamp('requested_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->unsignedInteger('duration_ms')->nullable();

            $table->timestamps();

            $table->index(['store_id', 'created_at']);
            $table->index(['source']);
            $table->index(['status']);
            $table->index(['printer_name']);
            $table->index(['ip_address']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_print_logs');
    }
};
