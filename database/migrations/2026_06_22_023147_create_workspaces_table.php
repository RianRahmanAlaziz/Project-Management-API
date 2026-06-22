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
        Schema::create('workspaces', function (Blueprint $table) {
            $table->id();

            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();

            $table->string('name', 100);
            $table->string('slug', 160)->unique();
            $table->text('description')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index('owner_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workspaces');
    }
};
