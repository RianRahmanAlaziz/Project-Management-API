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
        Schema::create('kanban_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100);
            $table->string('color', 20)->nullable();
            $table->boolean('enabled')->default(true);
            $table->unsignedInteger('position');
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            $table->unique([
                'project_id',
                'position',
            ]);

            $table->unique([
                'project_id',
                'name',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kanban_columns');
    }
};
