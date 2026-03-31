<?php

declare(strict_types=1);

namespace Devtools\DocSystem\Livewire;

use Devtools\DocSystem\Models\DocFile;
use Devtools\DocSystem\Models\DocFileVersion;
use Devtools\DocSystem\Models\DocNote;
use Devtools\DocSystem\Models\DocPage;
use Devtools\DocSystem\Models\DocVersion;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DocSystemAdmin extends Component
{
    public string $search       = '';
    public ?int $selectedPageId = null;
    public string $activeTab    = 'versions';

    public function mount(): void
    {
        abort_if(app()->environment('production'), 404);
        abort_if(! Auth::check(), 403);
    }

    // ── Computed ──────────────────────────────────────────────────────────────

    #[Computed]
    public function pages(): \Illuminate\Database\Eloquent\Collection
    {
        return DocPage::query()
            ->when($this->search, fn ($q) => $q->where(function ($inner) {
                $inner->where('url_path', 'like', '%' . $this->search . '%')
                      ->orWhere('query_string', 'like', '%' . $this->search . '%');
            }))
            ->withCount(['versions', 'notes', 'files', 'events'])
            ->orderBy('url_path')
            ->orderBy('query_string')
            ->get();
    }

    #[Computed]
    public function selectedPage(): ?DocPage
    {
        if (! $this->selectedPageId) {
            return null;
        }

        return DocPage::with([
            'versions'        => fn ($q) => $q->orderByDesc('created_at'),
            'notes'           => fn ($q) => $q->orderByDesc('created_at'),
            'files.fileVersions',
            'events'          => fn ($q) => $q->orderByDesc('created_at'),
        ])->find($this->selectedPageId);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pages'    => DocPage::count(),
            'versions' => DocVersion::count(),
            'notes'    => DocNote::count(),
            'files'    => DocFileVersion::count(),
        ];
    }

    // ── Actions ───────────────────────────────────────────────────────────────

    public function selectPage(int $id): void
    {
        $this->selectedPageId = $id;
        $this->activeTab      = 'versions';
        unset($this->selectedPage);
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function deletePage(int $id): void
    {
        DocPage::findOrFail($id)->delete();

        if ($this->selectedPageId === $id) {
            $this->selectedPageId = null;
        }

        unset($this->pages, $this->selectedPage, $this->stats);
    }

    public function deleteVersion(int $id): void
    {
        DocVersion::findOrFail($id)->delete();
        unset($this->selectedPage, $this->stats);
    }

    public function deleteNote(int $id): void
    {
        DocNote::findOrFail($id)->delete();
        unset($this->selectedPage, $this->stats);
    }

    public function deleteFileVersion(int $fileVersionId): void
    {
        $fv = DocFileVersion::findOrFail($fileVersionId);
        Storage::disk('public')->delete($fv->disk_path);

        $docFileId = $fv->doc_file_id;
        $fv->delete();

        // Remove the parent DocFile entity if it has no versions left
        $docFile = DocFile::find($docFileId);
        if ($docFile && $docFile->fileVersions()->count() === 0) {
            $docFile->delete();
        }

        unset($this->selectedPage, $this->stats);
    }

    // ── Render ────────────────────────────────────────────────────────────────

    public function render(): \Illuminate\View\View
    {
        return view('docsystem::livewire.doc-system-admin')
            ->layout('docsystem::layouts.admin');
    }
}
