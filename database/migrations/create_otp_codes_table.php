<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->string('identifier'); // email, phone, etc.
            $table->string('code', 10);
            $table->string('type')->nullable(); // login, register, reset, etc.
            $table->timestamp('expires_at');
            $table->boolean('used')->default(false);
            $table->timestamps();

            $table->index(['identifier', 'type', 'used']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otp_codes');
    }
};
