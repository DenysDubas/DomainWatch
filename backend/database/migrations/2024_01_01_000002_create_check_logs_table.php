<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->enum('status', ['up', 'down']);
            $table->unsignedSmallInteger('response_code')->nullable();
            $table->float('response_time')->comment('milliseconds');
            $table->text('error_message')->nullable();
            $table->timestamp('checked_at');
            $table->timestamps();

            $table->index(['domain_id', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('check_logs');
    }
};
