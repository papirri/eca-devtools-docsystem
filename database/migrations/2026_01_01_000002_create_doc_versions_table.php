<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_page_id')->constrained('doc_pages')->cascadeOnDelete();
            $table->string('version_number');
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('created_by')->nullable(); // user name/email snapshot
            $table->timestamps();

            $table->index(['doc_page_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_versions');
    }
};
