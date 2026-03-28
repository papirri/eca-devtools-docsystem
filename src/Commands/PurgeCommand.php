<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class PurgeCommand extends Command
{
    protected $signature = 'docsystem:purge';

    protected $description = 'Delete all DocSystem data: database tables and stored files. DEV ONLY.';

    /** Tables to drop in dependency order (children first). */
    private array $tables = [
        'doc_file_versions',
        'doc_files',
        'doc_notes',
        'doc_events',
        'doc_versions',
        'doc_pages',
    ];

    public function handle(): int
    {
        // ── Environment guard ───────────────────────────────────────────────
        if (app()->environment('production')) {
            $this->error('docsystem:purge refuses to run in production.');
            return self::FAILURE;
        }

        $this->warn('╔══════════════════════════════════════════════════╗');
        $this->warn('║        DocSystem PURGE — DESTRUCTIVE ACTION      ║');
        $this->warn('╚══════════════════════════════════════════════════╝');
        $this->newLine();
        $this->line('This command will:');
        $this->line('  • Drop all DocSystem database tables');
        $this->line('  • Delete all files from storage/app/public/doc-system');
        $this->newLine();

        if (! $this->confirm('Are you sure you want to continue? This cannot be undone.', false)) {
            $this->info('Purge cancelled.');
            return self::SUCCESS;
        }

        // ── Drop tables ─────────────────────────────────────────────────────
        $this->info('Dropping tables…');
        Schema::disableForeignKeyConstraints();
        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::drop($table);
                $this->line("  ✓ Dropped: {$table}");
            } else {
                $this->line("  – Skipped (not found): {$table}");
            }
        }
        Schema::enableForeignKeyConstraints();

        // ── Delete files ────────────────────────────────────────────────────
        $storagePath = config('docsystem.storage_path', 'doc-system');
        $this->info("Deleting files from storage/app/public/{$storagePath}…");

        if (Storage::disk('public')->exists($storagePath)) {
            Storage::disk('public')->deleteDirectory($storagePath);
            $this->line("  ✓ Deleted directory: {$storagePath}");
        } else {
            $this->line("  – Directory not found, skipping.");
        }

        $this->newLine();
        $this->info('DocSystem purge complete.');
        $this->line('You can now safely run: composer remove eca-devtools/docsystem');

        return self::SUCCESS;
    }
}
