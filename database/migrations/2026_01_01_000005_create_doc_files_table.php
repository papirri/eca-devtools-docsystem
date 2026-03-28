<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // doc_files groups all versions of a logical file entity
        Schema::create('doc_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_page_id')->constrained('doc_pages')->cascadeOnDelete();
            $table->string('name');         // logical name shown to user
            $table->string('extension');    // e.g. "php", "json"
            $table->boolean('is_text')->default(false); // text-diffable?
            $table->timestamps();

            $table->index('doc_page_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_files');
    }
};
