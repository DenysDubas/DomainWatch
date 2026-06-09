<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('url');
            $table->string('name');
            $table->unsignedSmallInteger('check_interval')->default(5)->comment('minutes');
            $table->unsignedSmallInteger('timeout')->default(10)->comment('seconds');
            $table->enum('method', ['GET', 'HEAD'])->default('GET');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_checked_at')->nullable();
            $table->enum('last_status', ['up', 'down'])->nullable();
            $table->unsignedSmallInteger('last_response_code')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
            $table->index('last_checked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
