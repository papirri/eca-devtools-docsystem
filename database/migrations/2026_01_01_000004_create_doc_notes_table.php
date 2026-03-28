<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_page_id')->constrained('doc_pages')->cascadeOnDelete();
            $table->enum('type', ['avance', 'pregunta', 'error', 'nota'])->default('nota');
            $table->text('content');
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->index(['doc_page_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_notes');
    }
};
