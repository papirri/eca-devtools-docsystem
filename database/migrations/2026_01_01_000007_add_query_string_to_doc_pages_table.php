<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doc_pages', function (Blueprint $table) {
            // Drop the old unique index on url_path alone
            $table->dropUnique(['url_path']);

            // Add query_string column (empty string = no query string)
            $table->string('query_string')->default('')->after('url_path');

            // New composite unique: same path + same query string = same page
            $table->unique(['url_path', 'query_string']);
        });
    }

    public function down(): void
    {
        Schema::table('doc_pages', function (Blueprint $table) {
            $table->dropUnique(['url_path', 'query_string']);
            $table->dropColumn('query_string');
            $table->unique('url_path');
        });
    }
};
