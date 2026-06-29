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
        Schema::create('print_jobs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('job_id')->unique()->index();
            $blueprint->unsignedBigInteger('order_id')->index();
            $blueprint->unsignedBigInteger('store_id')->index();
            $blueprint->text('asset_url');
            $blueprint->string('media_size')->default('4x6');
            $blueprint->string('product_name');
            $blueprint->integer('quantity')->default(1);
            $blueprint->string('customer_name')->nullable();
            $blueprint->json('settings')->nullable();
            $blueprint->string('status')->default('pending'); // pending, downloaded, printed, failed
            $blueprint->text('error_message')->nullable();
            $blueprint->timestamp('printed_at')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('print_jobs');
    }
};
