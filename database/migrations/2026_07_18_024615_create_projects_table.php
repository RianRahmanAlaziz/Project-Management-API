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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('workspace_id')->constrained()->cascadeOnDelete();
            $table->foreignId('owner_id')->constrained('users')->restrictOnDelete();

            $table->string('name', 100);
            $table->string('slug', 160);
            $table->text('description')->nullable();
            $table->string('status')->nullable();
            $table->string('color', 100)->nullable();
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique([
                'workspace_id',
                'slug',
            ]);
            $table->index('workspace_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
