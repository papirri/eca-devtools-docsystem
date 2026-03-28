<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_file_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_file_id')->constrained('doc_files')->cascadeOnDelete();
            $table->string('version_number');
            $table->string('disk_path');        // relative path inside storage disk
            $table->string('original_name');    // original filename at upload
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->string('mime_type')->nullable();
            $table->string('uploaded_by')->nullable();
            $table->timestamps();

            $table->index(['doc_file_id', 'version_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_file_versions');
    }
};
