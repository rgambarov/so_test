<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('external_id', 64)->unique();
            $table->dateTime('created_at'); // Date from the source file.
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('city')->nullable();
            $table->string('source')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('product')->nullable();
            $table->decimal('budget_uah', 14, 2)->nullable();
            $table->string('status', 64)->nullable();
            $table->string('manager')->nullable();
            $table->text('comment')->nullable();
            $table->dateTime('next_contact_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
