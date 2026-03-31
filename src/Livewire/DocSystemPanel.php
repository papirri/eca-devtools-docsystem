<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Livewire;

use Devtools\DocSystem\Models\DocFile;
use Devtools\DocSystem\Models\DocFileVersion;
use Devtools\DocSystem\Models\DocNote;
use Devtools\DocSystem\Models\DocPage;
use Devtools\DocSystem\Models\DocVersion;
use Devtools\DocSystem\Services\DiffService;
use Devtools\DocSystem\Services\DocPageService;
use Devtools\DocSystem\Services\TimelineService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithFileUploads;

class DocSystemPanel extends Component
{
    use WithFileUploads;

    // ── Panel state ──────────────────────────────────────────────────────────
    public bool $open = false;
    public string $activeTab = 'documentation';

    // ── Current page ─────────────────────────────────────────────────────────
    public ?int $docPageId = null;

    // ── Documentation / version form ─────────────────────────────────────────
    public string $versionTitle = '';
    public string $versionNumber = '';
    public string $versionDescription = '';
    public ?int $editingVersionId = null;
    public bool $showVersionForm = false;

    // ── Notes form ────────────────────────────────────────────────────────────
    public string $noteType = 'nota';
    public string $noteContent = '';
    public ?int $editingNoteId = null;

    // ── Files form ────────────────────────────────────────────────────────────
    public string $fileName = '';
    public ?int $uploadTargetFileId = null; // null = new file, non-null = add version
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $uploadedFile = null;

    // ── Diff viewer ──────────────────────────────────────────────────────────
    public ?int $diffOldVersionId = null;
    public ?int $diffNewVersionId = null;
    public ?int $diffFileId = null;
    public bool $showDiff = false;

    // ─────────────────────────────────────────────────────────────────────────

    public function mount(): void
    {
        if (app()->environment('production')) {
            return;
        }

        if (! Auth::check()) {
            return;
        }

        $service = new DocPageService();
        // Use the route URI pattern (e.g. "cp/documentos/{id}") so that all
        // records for the same resource share one DocPage. Fall back to the
        // literal path when no named route is matched (e.g. custom 404 pages).
        $urlPath = request()->route()?->uri() ?? request()->path();
        $page = $service->findOrCreateByPath($urlPath, request()->getQueryString() ?? '');
        $this->docPageId = $page->id;
    }

    /**
     * Called from Alpine after every wire:navigate navigation.
     * The browser passes the new path and search string directly so we don't
     * rely on request() (which still reflects the previous Livewire request).
     *
     * @param string $pathname  e.g. "/cp/documentos/2"
     * @param string $search    e.g. "?tab=scans"  (may be empty)
     */
    public function onNavigated(string $pathname, string $search = ''): void
    {
        if (app()->environment('production') || ! Auth::check()) {
            return;
        }

        // Strip leading slash so it matches request()->path() conventions
        $path = ltrim($pathname, '/');
        $query = ltrim($search, '?');

        // Try to match a named route so we get the pattern (e.g. cp/documentos/{id})
        $urlPath = $path;
        try {
            $routes = app('router')->getRoutes();
            $matched = $routes->match(
                \Illuminate\Http\Request::create('/' . $path)
            );
            if ($matched) {
                $urlPath = $matched->uri();
            }
        } catch (\Throwable) {
            // No match — keep the literal path
        }

        $service = new DocPageService();
        $page = $service->findOrCreateByPath($urlPath, $query);
        $this->docPageId = $page->id;

        // Reset all form state so stale data from the previous page is cleared
        $this->unsetComputedPropertyCache();
        $this->resetFormState();
    }

    private function resetFormState(): void
    {
        $this->versionTitle       = '';
        $this->versionNumber      = '';
        $this->versionDescription = '';
        $this->editingVersionId   = null;
        $this->showVersionForm    = false;
        $this->noteType           = 'nota';
        $this->noteContent        = '';
        $this->editingNoteId      = null;
        $this->fileName           = '';
        $this->uploadTargetFileId = null;
        $this->uploadedFile       = null;
        $this->resetDiff();
    }

    // ── Computed ─────────────────────────────────────────────────────────────

    #[Computed]
    public function page(): ?DocPage
    {
        if (! $this->docPageId) {
            return null;
        }
        return DocPage::with(['versions', 'events', 'notes', 'files.fileVersions'])->find($this->docPageId);
    }

    #[Computed]
    public function diffResult(): array
    {
        if (! $this->showDiff || ! $this->diffOldVersionId || ! $this->diffNewVersionId) {
            return [];
        }

        $old = DocFileVersion::find($this->diffOldVersionId);
        $new = DocFileVersion::find($this->diffNewVersionId);

        if (! $old || ! $new) {
            return [];
        }

        $diffService = new DiffService();
        return $diffService->diff($old->readText(), $new->readText());
    }

    // ── Panel toggle ─────────────────────────────────────────────────────────

    public function togglePanel(): void
    {
        $this->open = ! $this->open;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetDiff();
    }

    // ── Documentation actions ────────────────────────────────────────────────

    public function openVersionForm(?int $versionId = null): void
    {
        $this->showVersionForm = true;

        if ($versionId) {
            $v = DocVersion::findOrFail($versionId);
            $this->editingVersionId = $v->id;
            $this->versionTitle = $v->title;
            $this->versionNumber = $v->version_number;
            $this->versionDescription = $v->description ?? '';
        } else {
            $this->editingVersionId = null;
            $this->versionTitle = '';
            $this->versionNumber = '';
            $this->versionDescription = '';
        }
    }

    public function cancelVersionForm(): void
    {
        $this->showVersionForm = false;
        $this->resetVersionForm();
    }

    public function saveVersion(): void
    {
        $this->validate([
            'versionTitle'       => 'required|string|max:255',
            'versionNumber'      => 'required|string|max:50',
            'versionDescription' => 'nullable|string',
        ]);

        $user = Auth::user();
        $page = $this->page;
        $timeline = new TimelineService();

        if ($this->editingVersionId) {
            $v = DocVersion::findOrFail($this->editingVersionId);
            $v->update([
                'title'          => $this->versionTitle,
                'version_number' => $this->versionNumber,
                'description'    => $this->versionDescription,
            ]);

            $timeline->record($page->id, 'updated', ['version_id' => $v->id]);
        } else {
            $v = DocVersion::create([
                'doc_page_id'    => $page->id,
                'title'          => $this->versionTitle,
                'version_number' => $this->versionNumber,
                'description'    => $this->versionDescription,
                'created_by'     => $user?->name ?? $user?->email,
            ]);

            $timeline->record($page->id, 'created', ['version_id' => $v->id]);
        }

        $this->showVersionForm = false;
        $this->resetVersionForm();
        unset($this->page); // clear computed cache
    }

    // ── Notes actions ────────────────────────────────────────────────────────

    public function openNoteEdit(int $noteId): void
    {
        $note = DocNote::findOrFail($noteId);
        $this->editingNoteId = $noteId;
        $this->noteType      = $note->type;
        $this->noteContent   = $note->content;
    }

    public function cancelNoteEdit(): void
    {
        $this->editingNoteId = null;
        $this->noteType      = 'nota';
        $this->noteContent   = '';
    }

    public function saveNote(): void
    {
        $this->validate([
            'noteType'    => 'required|in:avance,pregunta,error,nota',
            'noteContent' => 'required|string|max:5000',
        ]);

        $user     = Auth::user();
        $page     = $this->page;
        $timeline = new TimelineService();

        if ($this->editingNoteId) {
            $note = DocNote::findOrFail($this->editingNoteId);
            $note->update([
                'type'    => $this->noteType,
                'content' => $this->noteContent,
            ]);
            $this->editingNoteId = null;
        } else {
            $note = DocNote::create([
                'doc_page_id' => $page->id,
                'type'        => $this->noteType,
                'content'     => $this->noteContent,
                'created_by'  => $user?->name ?? $user?->email,
            ]);
            $timeline->record($page->id, 'note_added', ['note_id' => $note->id, 'type' => $note->type]);
        }

        $this->noteContent = '';
        $this->noteType    = 'nota';
        unset($this->page);
    }

    public function deleteNote(int $noteId): void
    {
        DocNote::findOrFail($noteId)->delete();
        unset($this->page);
    }

    // ── Files actions ────────────────────────────────────────────────────────

    public function prepareNewFile(): void
    {
        $this->uploadTargetFileId = null;
        $this->fileName = '';
        $this->uploadedFile = null;
    }

    public function prepareNewVersion(int $fileId): void
    {
        $this->uploadTargetFileId = $fileId;
        $this->uploadedFile = null;
    }

    public function uploadFile(): void
    {
        $this->validate([
            'uploadedFile' => 'required|file|max:' . config('docsystem.max_file_size'),
            'fileName'     => 'required_without:uploadTargetFileId|nullable|string|max:255',
        ]);

        $user = Auth::user();
        $page = $this->page;
        $timeline = new TimelineService();
        $storagePath = config('docsystem.storage_path', 'doc-system');

        $file = $this->uploadedFile;
        $extension = strtolower($file->getClientOriginalExtension());
        $textExtensions = config('docsystem.text_extensions', []);
        $isText = in_array($extension, $textExtensions, true);

        if ($this->uploadTargetFileId) {
            // Adding a new version to an existing file entity
            $docFile = DocFile::findOrFail($this->uploadTargetFileId);
            $eventType = 'file_updated';
        } else {
            // Creating a new file entity
            $docFile = DocFile::create([
                'doc_page_id' => $page->id,
                'name'        => $this->fileName,
                'extension'   => $extension,
                'is_text'     => $isText,
            ]);
            $eventType = 'file_uploaded';
        }

        // Determine next version number
        $latestVersion = $docFile->fileVersions()->first();
        $nextVersion = $latestVersion
            ? $this->incrementVersion($latestVersion->version_number)
            : '1.0';

        // Store the file
        $diskPath = $file->storeAs(
            $storagePath . '/' . $docFile->id,
            $nextVersion . '_' . $file->getClientOriginalName(),
            'public'
        );

        DocFileVersion::create([
            'doc_file_id'    => $docFile->id,
            'version_number' => $nextVersion,
            'disk_path'      => $diskPath,
            'original_name'  => $file->getClientOriginalName(),
            'size'           => $file->getSize(),
            'mime_type'      => $file->getMimeType(),
            'uploaded_by'    => $user?->name ?? $user?->email,
        ]);

        $timeline->record($page->id, $eventType, ['file_id' => $docFile->id, 'version' => $nextVersion]);

        $this->uploadedFile = null;
        $this->uploadTargetFileId = null;
        $this->fileName = '';
        unset($this->page);
    }

    public function deleteFileVersion(int $fileVersionId): void
    {
        $fv = DocFileVersion::findOrFail($fileVersionId);
        Storage::disk('public')->delete($fv->disk_path);

        $docFileId = $fv->doc_file_id;
        $fv->delete();

        $page = $this->page;
        $timeline = new TimelineService();
        $timeline->record($page->id, 'file_deleted', ['file_version_id' => $fileVersionId]);

        // If no versions remain, delete the file entity too
        if (DocFile::find($docFileId)?->fileVersions()->count() === 0) {
            DocFile::find($docFileId)?->delete();
        }

        unset($this->page);
    }

    // ── Diff actions ─────────────────────────────────────────────────────────

    public function openDiff(int $fileId, int $oldVersionId, int $newVersionId): void
    {
        $this->diffFileId = $fileId;
        $this->diffOldVersionId = $oldVersionId;
        $this->diffNewVersionId = $newVersionId;
        $this->showDiff = true;
        unset($this->diffResult);
    }

    public function closeDiff(): void
    {
        $this->resetDiff();
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function resetDiff(): void
    {
        $this->showDiff = false;
        $this->diffFileId = null;
        $this->diffOldVersionId = null;
        $this->diffNewVersionId = null;
        unset($this->diffResult);
    }

    private function resetVersionForm(): void
    {
        $this->versionTitle = '';
        $this->versionNumber = '';
        $this->versionDescription = '';
        $this->editingVersionId = null;
    }

    /**
     * Increment a simple version string like "1.0" -> "1.1", "1.9" -> "1.10".
     */
    private function incrementVersion(string $version): string
    {
        $parts = explode('.', $version);
        if (count($parts) >= 2) {
            $parts[count($parts) - 1] = (string) ((int) $parts[count($parts) - 1] + 1);
            return implode('.', $parts);
        }
        return (string) ((int) $version + 1);
    }

    public function render(): \Illuminate\View\View
    {
        return view('docsystem::livewire.doc-system-panel');
    }
}
