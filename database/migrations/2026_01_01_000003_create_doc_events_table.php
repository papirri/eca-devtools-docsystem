<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_page_id')->constrained('doc_pages')->cascadeOnDelete();
            $table->string('event_type'); // created, updated, note_added, file_uploaded, file_updated, file_deleted
            $table->string('performed_by')->nullable(); // user name/email snapshot
            $table->json('metadata')->nullable();       // flexible context (note id, file id, etc.)
            $table->timestamps();

            $table->index(['doc_page_id', 'event_type']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_events');
    }
};
