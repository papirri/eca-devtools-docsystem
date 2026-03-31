{{-- DocSystem Admin — full management panel --}}
<div class="flex h-screen overflow-hidden font-sans text-sm">

    {{-- ══ LEFT SIDEBAR ══════════════════════════════════════════════════════ --}}
    <aside class="w-80 shrink-0 flex flex-col bg-white dark:bg-gray-900 border-r border-gray-200 dark:border-gray-700">

        {{-- Header --}}
        <div class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
            <h1 class="text-base font-bold text-white tracking-tight">DocSystem Admin</h1>
            <div class="mt-2 flex gap-3 text-xs text-indigo-200">
                <span>{{ $this->stats['pages'] }} pages</span>
                <span>{{ $this->stats['versions'] }} versions</span>
                <span>{{ $this->stats['notes'] }} notes</span>
                <span>{{ $this->stats['files'] }} files</span>
            </div>
        </div>

        {{-- Search --}}
        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
            <input
                type="text"
                wire:model.live.debounce.300ms="search"
                placeholder="Filter by URL or query string…"
                class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-gray-700
                       focus:outline-none focus:ring-2 focus:ring-indigo-400 bg-white dark:bg-gray-800
                       text-gray-700 dark:text-gray-200 placeholder-gray-400"
            />
        </div>

        {{-- Pages list --}}
        <div class="flex-1 overflow-y-auto divide-y divide-gray-100 dark:divide-gray-800">
            @forelse($this->pages as $page)
                <div
                    wire:key="page-{{ $page->id }}"
                    class="group flex items-start justify-between gap-2 px-4 py-3 cursor-pointer
                           transition-colors hover:bg-indigo-50 dark:hover:bg-gray-800
                           {{ $selectedPageId === $page->id
                               ? 'bg-indigo-50 dark:bg-gray-800 border-l-2 border-indigo-500'
                               : 'border-l-2 border-transparent' }}"
                    wire:click="selectPage({{ $page->id }})"
                >
                    <div class="min-w-0 flex-1">
                        <p class="font-medium text-gray-800 dark:text-gray-100 truncate leading-snug">
                            /{{ $page->url_path }}
                        </p>
                        @if($page->query_string)
                            <p class="text-xs text-indigo-500 dark:text-indigo-400 truncate mt-0.5">
                                ?{{ $page->query_string }}
                            </p>
                        @endif
                        <div class="flex gap-3 mt-1">
                            <span class="text-xs text-gray-400">{{ $page->versions_count }}v</span>
                            <span class="text-xs text-gray-400">{{ $page->notes_count }}n</span>
                            <span class="text-xs text-gray-400">{{ $page->files_count }}f</span>
                            <span class="text-xs text-gray-400">{{ $page->events_count }}e</span>
                        </div>
                    </div>
                    <button
                        wire:click.stop="deletePage({{ $page->id }})"
                        wire:confirm="Delete this page and ALL its data?"
                        class="shrink-0 mt-0.5 text-gray-200 dark:text-gray-700 hover:text-red-500
                               dark:hover:text-red-400 transition-colors opacity-0 group-hover:opacity-100"
                        title="Delete page"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                     4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                    </button>
                </div>
            @empty
                <div class="px-4 py-10 text-center text-gray-400 text-xs">
                    @if($search) No pages match "{{ $search }}" @else No pages tracked yet @endif
                </div>
            @endforelse
        </div>
    </aside>

    {{-- ══ RIGHT CONTENT ═════════════════════════════════════════════════════ --}}
    <main class="flex-1 flex flex-col overflow-hidden bg-gray-50 dark:bg-gray-950">

        @if($this->selectedPage)

            {{-- Page header --}}
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="min-w-0">
                        <h2 class="font-bold text-gray-900 dark:text-white text-base leading-snug truncate">
                            /{{ $this->selectedPage->url_path }}@if($this->selectedPage->query_string)?{{ $this->selectedPage->query_string }}@endif
                        </h2>
                        <p class="text-xs text-gray-400 mt-1">
                            Page #{{ $this->selectedPage->id }}
                            · Created {{ $this->selectedPage->created_at->format('d M Y') }}
                            · Updated {{ $this->selectedPage->updated_at->diffForHumans() }}
                        </p>
                    </div>
                    <button
                        wire:click="deletePage({{ $this->selectedPage->id }})"
                        wire:confirm="Delete this page and ALL its data permanently?"
                        class="shrink-0 flex items-center gap-1.5 text-xs text-red-500 hover:text-red-700
                               border border-red-200 hover:border-red-400 px-3 py-1.5 rounded-lg
                               transition-colors"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                     4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Delete page
                    </button>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700 px-6 flex gap-0">
                @foreach([
                    'versions' => ['label' => 'Versions',  'count' => $this->selectedPage->versions->count()],
                    'notes'    => ['label' => 'Notes',     'count' => $this->selectedPage->notes->count()],
                    'files'    => ['label' => 'Files',     'count' => $this->selectedPage->files->count()],
                    'timeline' => ['label' => 'Timeline',  'count' => $this->selectedPage->events->count()],
                ] as $tab => $meta)
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        class="px-4 py-3 text-xs font-semibold transition-colors focus:outline-none
                               {{ $activeTab === $tab
                                   ? 'text-indigo-600 dark:text-indigo-400 border-b-2 border-indigo-600 dark:border-indigo-400 -mb-px'
                                   : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}"
                    >
                        {{ $meta['label'] }}
                        <span class="ml-1 text-xs {{ $activeTab === $tab ? 'text-indigo-400' : 'text-gray-300 dark:text-gray-600' }}">
                            ({{ $meta['count'] }})
                        </span>
                    </button>
                @endforeach
            </div>

            {{-- Tab content --}}
            <div class="flex-1 overflow-y-auto p-6">

                {{-- ════ VERSIONS ════ --}}
                @if($activeTab === 'versions')
                    @forelse($this->selectedPage->versions as $version)
                        <div wire:key="ver-{{ $version->id }}"
                             class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700
                                    p-4 mb-3 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center flex-wrap gap-2">
                                        <span class="font-semibold text-gray-800 dark:text-gray-200">
                                            {{ $version->title }}
                                        </span>
                                        <span class="text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700
                                                     dark:text-indigo-300 px-2 py-0.5 rounded-full font-mono">
                                            v{{ $version->version_number }}
                                        </span>
                                    </div>
                                    @if($version->description)
                                        <p class="text-gray-600 dark:text-gray-400 mt-2 whitespace-pre-wrap leading-relaxed">
                                            {{ $version->description }}
                                        </p>
                                    @endif
                                    <p class="text-xs text-gray-400 mt-2">
                                        @if($version->created_by)By {{ $version->created_by }} · @endif
                                        {{ $version->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <button
                                    wire:click="deleteVersion({{ $version->id }})"
                                    wire:confirm="Delete this version?"
                                    class="shrink-0 text-gray-300 dark:text-gray-600 hover:text-red-500
                                           dark:hover:text-red-400 transition-colors"
                                    title="Delete version"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                                 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-400 text-sm">No versions recorded yet.</div>
                    @endforelse

                {{-- ════ NOTES ════ --}}
                @elseif($activeTab === 'notes')
                    @php
                        $noteColors = [
                            'avance'   => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'pregunta' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'error'    => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                            'nota'     => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                        ];
                    @endphp
                    @forelse($this->selectedPage->notes as $note)
                        <div wire:key="note-{{ $note->id }}"
                             class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700
                                    p-4 mb-3 shadow-sm">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <span class="inline-block text-xs font-semibold px-2.5 py-0.5 rounded-full
                                                 {{ $noteColors[$note->type] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ ucfirst($note->type) }}
                                    </span>
                                    <p class="text-gray-700 dark:text-gray-300 mt-2 whitespace-pre-wrap leading-relaxed">
                                        {{ $note->content }}
                                    </p>
                                    <p class="text-xs text-gray-400 mt-2">
                                        @if($note->created_by)By {{ $note->created_by }} · @endif
                                        {{ $note->created_at->format('d M Y, H:i') }}
                                    </p>
                                </div>
                                <button
                                    wire:click="deleteNote({{ $note->id }})"
                                    wire:confirm="Delete this note?"
                                    class="shrink-0 text-gray-300 dark:text-gray-600 hover:text-red-500
                                           dark:hover:text-red-400 transition-colors"
                                    title="Delete note"
                                >
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                              d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                                 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-400 text-sm">No notes yet.</div>
                    @endforelse

                {{-- ════ FILES ════ --}}
                @elseif($activeTab === 'files')
                    @forelse($this->selectedPage->files as $file)
                        <div wire:key="file-{{ $file->id }}"
                             class="bg-white dark:bg-gray-900 rounded-xl border border-gray-200 dark:border-gray-700
                                    p-4 mb-4 shadow-sm">
                            {{-- File header --}}
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <span class="font-semibold text-gray-800 dark:text-gray-200">
                                        {{ $file->name }}
                                    </span>
                                    <span class="text-xs bg-gray-100 dark:bg-gray-700 text-gray-500
                                                 dark:text-gray-400 px-2 py-0.5 rounded font-mono">
                                        .{{ $file->extension }}
                                    </span>
                                    @if($file->is_text)
                                        <span class="text-xs bg-blue-50 dark:bg-blue-900/30 text-blue-500
                                                     dark:text-blue-400 px-2 py-0.5 rounded">
                                            text
                                        </span>
                                    @endif
                                </div>
                                <span class="text-xs text-gray-400">
                                    {{ $file->fileVersions->count() }}
                                    {{ $file->fileVersions->count() === 1 ? 'version' : 'versions' }}
                                </span>
                            </div>

                            {{-- File versions --}}
                            <div class="space-y-2 border-t border-gray-100 dark:border-gray-700 pt-3">
                                @forelse($file->fileVersions as $fv)
                                    <div wire:key="fv-{{ $fv->id }}"
                                         class="flex items-center justify-between gap-4 py-1">
                                        <div class="flex items-center gap-3 min-w-0">
                                            <span class="text-xs font-mono text-indigo-600 dark:text-indigo-400 shrink-0">
                                                v{{ $fv->version_number }}
                                            </span>
                                            <span class="text-xs text-gray-600 dark:text-gray-400 truncate">
                                                {{ $fv->original_name }}
                                            </span>
                                            <span class="text-xs text-gray-400 shrink-0">
                                                {{ number_format($fv->size / 1024, 1) }} KB
                                            </span>
                                            @if($fv->uploaded_by)
                                                <span class="text-xs text-gray-400 shrink-0 hidden sm:inline">
                                                    {{ $fv->uploaded_by }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3 shrink-0">
                                            <span class="text-xs text-gray-400">
                                                {{ $fv->created_at->format('d M Y') }}
                                            </span>
                                            <a href="{{ $fv->downloadUrl() }}"
                                               target="_blank"
                                               class="text-xs text-indigo-500 hover:text-indigo-700
                                                      dark:text-indigo-400 dark:hover:text-indigo-200 transition-colors">
                                                Download
                                            </a>
                                            <button
                                                wire:click="deleteFileVersion({{ $fv->id }})"
                                                wire:confirm="Delete this file version?"
                                                class="text-gray-300 dark:text-gray-600 hover:text-red-500
                                                       dark:hover:text-red-400 transition-colors"
                                                title="Delete version"
                                            >
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                     viewBox="0 0 24 24" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5
                                                             4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                @empty
                                    <p class="text-xs text-gray-400">No versions uploaded.</p>
                                @endforelse
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-16 text-gray-400 text-sm">No files uploaded yet.</div>
                    @endforelse

                {{-- ════ TIMELINE ════ --}}
                @elseif($activeTab === 'timeline')
                    @php
                        $eventBadge = [
                            'created'       => 'bg-indigo-100 text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-300',
                            'updated'       => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                            'note_added'    => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                            'file_uploaded' => 'bg-purple-100 text-purple-700 dark:bg-purple-900/40 dark:text-purple-300',
                            'file_updated'  => 'bg-amber-100 text-amber-700 dark:bg-amber-900/40 dark:text-amber-300',
                            'file_deleted'  => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                        ];
                    @endphp
                    <div class="space-y-2">
                        @forelse($this->selectedPage->events as $event)
                            <div wire:key="ev-{{ $event->id }}"
                                 class="flex items-start gap-3 bg-white dark:bg-gray-900 rounded-xl
                                        border border-gray-200 dark:border-gray-700 p-3 shadow-sm">
                                <span class="shrink-0 mt-0.5 text-xs font-semibold px-2.5 py-0.5 rounded-full
                                             {{ $eventBadge[$event->event_type] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ str_replace('_', ' ', $event->event_type) }}
                                </span>
                                <div class="min-w-0 flex-1">
                                    <p class="text-xs text-gray-400">
                                        @if($event->performed_by){{ $event->performed_by }} · @endif
                                        {{ $event->created_at->format('d M Y, H:i') }}
                                    </p>
                                    @if($event->metadata)
                                        <div class="mt-1 flex gap-1.5 flex-wrap">
                                            @foreach($event->metadata as $key => $val)
                                                <span class="text-xs bg-gray-100 dark:bg-gray-800 text-gray-500
                                                             dark:text-gray-400 px-2 py-0.5 rounded font-mono">
                                                    {{ $key }}: {{ $val }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-16 text-gray-400 text-sm">No events recorded yet.</div>
                        @endforelse
                    </div>
                @endif

            </div>

        @else
            {{-- Empty state --}}
            <div class="flex-1 flex flex-col items-center justify-center text-gray-400 gap-4">
                <svg class="w-16 h-16 text-gray-200 dark:text-gray-800" fill="none" stroke="currentColor"
                     viewBox="0 0 24 24" stroke-width="1">
                    <path stroke-linecap="round" stroke-linejoin="round"
                          d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm">Select a page from the left to view its full content</p>
            </div>
        @endif

    </main>
</div>
