{{-- DocSystem Panel — dev-only floating widget --}}
@php
    use Illuminate\Support\Facades\Auth;
    if (app()->environment('production') || ! Auth::check()) {
        return;
    }
@endphp

<div x-data="{ open: @entangle('open') }">

    {{-- ── Floating trigger button ────────────────────────────────────────── --}}
    <button
        wire:click="togglePanel"
        title="DocSystem"
        class="fixed bottom-6 right-6 z-[9990] flex items-center justify-center w-12 h-12
               rounded-full bg-indigo-600 text-white shadow-xl hover:bg-indigo-700
               transition-colors focus:outline-none focus:ring-2 focus:ring-indigo-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none"
             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M9 12h6m-6 4h6m2 4H7a2 2 0 01-2-2V6a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/>
        </svg>
    </button>

    {{-- ── Modal overlay ──────────────────────────────────────────────────── --}}
    <div
        x-show="open"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-[9991] flex items-end justify-end p-6"
        style="display: none;">

        {{-- semi-transparent backdrop --}}
        <div
            @click="open = false"
            class="absolute inset-0 bg-black/40 backdrop-blur-sm">
        </div>

        {{-- Panel card --}}
        <div
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="translate-y-4 opacity-0"
            x-transition:enter-end="translate-y-0 opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="translate-y-0 opacity-100"
            x-transition:leave-end="translate-y-4 opacity-0"
            class="relative z-10 w-full max-w-2xl max-h-[85vh] flex flex-col
                   bg-white dark:bg-gray-900 rounded-2xl shadow-2xl overflow-hidden">

            {{-- Header --}}
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-indigo-600">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-200">DocSystem</p>
                    <h2 class="text-white font-bold text-sm truncate max-w-xs">
                        /{{ $this->page?->url_path ?? request()->path() }}@if($this->page?->query_string)?{{ $this->page->query_string }}@endif
                    </h2>
                </div>
                <button wire:click="togglePanel"
                        class="text-indigo-200 hover:text-white transition-colors focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800">
                @foreach([
                    'documentation' => 'Documentation',
                    'timeline'      => 'Timeline',
                    'notes'         => 'Notes',
                    'files'         => 'Files',
                ] as $tab => $label)
                    <button
                        wire:click="setTab('{{ $tab }}')"
                        class="px-4 py-3 text-xs font-semibold transition-colors focus:outline-none
                               {{ $activeTab === $tab
                                   ? 'text-indigo-600 border-b-2 border-indigo-600 -mb-px bg-white dark:bg-gray-900 dark:text-indigo-400'
                                   : 'text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>

            {{-- Tab content --}}
            <div class="flex-1 overflow-y-auto p-5 text-sm text-gray-800 dark:text-gray-200">

                {{-- ════════ DOCUMENTATION TAB ════════ --}}
                @if($activeTab === 'documentation')
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Versions</h3>
                            <button wire:click="openVersionForm()"
                                    class="text-xs bg-indigo-600 text-white px-3 py-1.5 rounded-lg hover:bg-indigo-700 transition-colors">
                                + New version
                            </button>
                        </div>

                        @if($showVersionForm)
                        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Version number</label>
                                <input wire:model="versionNumber" type="text" placeholder="e.g. 1.0"
                                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                              bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                @error('versionNumber') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Title</label>
                                <input wire:model="versionTitle" type="text" placeholder="Version title"
                                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                              bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                @error('versionTitle') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Description</label>
                                <textarea wire:model="versionDescription" rows="4" placeholder="What changed in this version?"
                                          class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                                 bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none">
                                </textarea>
                            </div>
                            <div class="flex gap-2">
                                <button wire:click="saveVersion"
                                        class="text-xs bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                    Save
                                </button>
                                <button wire:click="cancelVersionForm"
                                        class="text-xs text-gray-500 hover:text-gray-700 dark:hover:text-gray-300 px-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 transition-colors">
                                    Cancel
                                </button>
                            </div>
                        </div>
                        @endif

                        @forelse($this->page?->versions ?? [] as $version)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 hover:border-indigo-300 transition-colors">
                            <div class="flex items-start justify-between gap-2">
                                <div>
                                    <span class="inline-block text-xs bg-indigo-100 dark:bg-indigo-900 text-indigo-700 dark:text-indigo-300
                                                 font-semibold px-2 py-0.5 rounded-full">v{{ $version->version_number }}</span>
                                    <span class="ml-2 font-medium">{{ $version->title }}</span>
                                </div>
                                <button wire:click="openVersionForm({{ $version->id }})"
                                        class="text-xs text-gray-400 hover:text-indigo-600 transition-colors shrink-0">Edit</button>
                            </div>
                            @if($version->description)
                            <p class="mt-2 text-gray-600 dark:text-gray-400 text-xs leading-relaxed whitespace-pre-line">
                                {{ $version->description }}
                            </p>
                            @endif
                            <div class="mt-2 text-xs text-gray-400">
                                by {{ $version->created_by ?? '—' }} · {{ $version->created_at->diffForHumans() }}
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-400 text-xs text-center py-6">No versions yet. Create the first one!</p>
                        @endforelse
                    </div>
                @endif

                {{-- ════════ TIMELINE TAB ════════ --}}
                @if($activeTab === 'timeline')
                    <div class="space-y-3">
                        @php
                            $iconMap = [
                                'created'       => ['bg' => 'bg-green-100 text-green-600',  'icon' => '✨'],
                                'updated'       => ['bg' => 'bg-blue-100 text-blue-600',    'icon' => '✏️'],
                                'note_added'    => ['bg' => 'bg-yellow-100 text-yellow-600','icon' => '📝'],
                                'file_uploaded' => ['bg' => 'bg-purple-100 text-purple-600','icon' => '📎'],
                                'file_updated'  => ['bg' => 'bg-orange-100 text-orange-600','icon' => '🔄'],
                                'file_deleted'  => ['bg' => 'bg-red-100 text-red-600',      'icon' => '🗑️'],
                            ];
                        @endphp

                        @forelse($this->page?->events ?? [] as $event)
                        @php $ic = $iconMap[$event->event_type] ?? ['bg' => 'bg-gray-100 text-gray-600', 'icon' => '•']; @endphp
                        <div class="flex gap-3 items-start">
                            <span class="text-sm w-8 h-8 flex items-center justify-center rounded-full shrink-0 {{ $ic['bg'] }}">
                                {{ $ic['icon'] }}
                            </span>
                            <div class="flex-1 min-w-0">
                                <p class="font-medium capitalize text-xs">{{ str_replace('_', ' ', $event->event_type) }}</p>
                                <p class="text-xs text-gray-400">
                                    {{ $event->performed_by ?? 'Unknown' }} · {{ $event->created_at->diffForHumans() }}
                                </p>
                                @if($event->metadata)
                                <pre class="mt-1 text-xs bg-gray-100 dark:bg-gray-800 p-2 rounded overflow-x-auto">{{ json_encode($event->metadata, JSON_PRETTY_PRINT) }}</pre>
                                @endif
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-400 text-xs text-center py-6">No timeline events yet.</p>
                        @endforelse
                    </div>
                @endif

                {{-- ════════ NOTES TAB ════════ --}}
                @if($activeTab === 'notes')
                    <div class="space-y-4">
                        {{-- Add note form --}}
                        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-3">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300 text-xs uppercase tracking-wider">Add a note</h3>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Type</label>
                                <select wire:model="noteType"
                                        class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                               bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                    @foreach(config('docsystem.note_types', []) as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Content</label>
                                <textarea wire:model="noteContent" rows="3" placeholder="Write your note..."
                                          class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                                 bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400 resize-none">
                                </textarea>
                                @error('noteContent') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <button wire:click="saveNote"
                                    class="text-xs bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                Add note
                            </button>
                        </div>

                        {{-- Notes list --}}
                        @php
                            $noteBadge = [
                                'avance'   => 'bg-green-100  text-green-700',
                                'pregunta' => 'bg-blue-100   text-blue-700',
                                'error'    => 'bg-red-100    text-red-700',
                                'nota'     => 'bg-gray-100   text-gray-700',
                            ];
                        @endphp
                        @forelse($this->page?->notes ?? [] as $note)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4">
                            <div class="flex items-center justify-between gap-2">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $noteBadge[$note->type] ?? 'bg-gray-100 text-gray-700' }}">
                                    {{ config('docsystem.note_types.' . $note->type, $note->type) }}
                                </span>
                                <button wire:click="deleteNote({{ $note->id }})"
                                        wire:confirm="Delete this note?"
                                        class="text-xs text-red-400 hover:text-red-600 transition-colors">Delete</button>
                            </div>
                            <p class="mt-2 text-xs text-gray-700 dark:text-gray-300 whitespace-pre-line leading-relaxed">{{ $note->content }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $note->created_by ?? '—' }} · {{ $note->created_at->diffForHumans() }}</p>
                        </div>
                        @empty
                        <p class="text-gray-400 text-xs text-center py-4">No notes yet.</p>
                        @endforelse
                    </div>
                @endif

                {{-- ════════ FILES TAB ════════ --}}
                @if($activeTab === 'files')
                    @if($showDiff)
                    {{-- ── Diff viewer ── --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300">File diff</h3>
                            <button wire:click="closeDiff"
                                    class="text-xs text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors">
                                ← Back
                            </button>
                        </div>
                        <div class="rounded-xl overflow-hidden border border-gray-200 dark:border-gray-700 font-mono text-xs">
                            <table class="w-full border-collapse">
                                <tbody>
                                @foreach($this->diffResult as $line)
                                @php
                                    $bg = match($line['type']) {
                                        'added'   => 'bg-green-50  dark:bg-green-950  text-green-800  dark:text-green-300',
                                        'removed' => 'bg-red-50    dark:bg-red-950    text-red-800    dark:text-red-300',
                                        default   => 'bg-white     dark:bg-gray-900   text-gray-600   dark:text-gray-400',
                                    };
                                    $prefix = match($line['type']) {
                                        'added'   => '+',
                                        'removed' => '-',
                                        default   => ' ',
                                    };
                                @endphp
                                <tr class="{{ $bg }}">
                                    <td class="w-8 text-center select-none opacity-40 border-r border-gray-200 dark:border-gray-700 px-1 py-0.5">
                                        {{ $line['lineOld'] ?? '' }}
                                    </td>
                                    <td class="w-8 text-center select-none opacity-40 border-r border-gray-200 dark:border-gray-700 px-1 py-0.5">
                                        {{ $line['lineNew'] ?? '' }}
                                    </td>
                                    <td class="w-4 text-center select-none font-bold px-1 py-0.5">{{ $prefix }}</td>
                                    <td class="px-2 py-0.5 whitespace-pre-wrap break-all">{{ $line['line'] }}</td>
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @else
                    {{-- ── Files list ── --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-semibold text-gray-700 dark:text-gray-300">Attached files</h3>
                        </div>

                        {{-- Upload form --}}
                        <div class="bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-xl p-4 space-y-3">
                            <h4 class="text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                                {{ $uploadTargetFileId ? 'Upload new version' : 'Upload new file' }}
                            </h4>

                            @if(! $uploadTargetFileId)
                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Display name</label>
                                <input wire:model="fileName" type="text" placeholder="e.g. Design mockup"
                                       class="w-full text-sm border border-gray-300 dark:border-gray-600 rounded-lg px-3 py-2
                                              bg-white dark:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-400" />
                                @error('fileName') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            @endif

                            <div>
                                <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">File</label>
                                <input wire:model="uploadedFile" type="file"
                                       class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg
                                              file:border-0 file:text-xs file:font-medium file:bg-indigo-50 file:text-indigo-700
                                              hover:file:bg-indigo-100 transition-colors" />
                                @error('uploadedFile') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                                <div wire:loading wire:target="uploadedFile" class="text-xs text-indigo-500 mt-1">Uploading…</div>
                            </div>

                            <div class="flex gap-2">
                                <button wire:click="uploadFile"
                                        class="text-xs bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors">
                                    Upload
                                </button>
                                @if($uploadTargetFileId)
                                <button wire:click="prepareNewFile"
                                        class="text-xs border border-gray-300 dark:border-gray-600 px-4 py-2 rounded-lg text-gray-500
                                               hover:text-gray-700 transition-colors">
                                    Cancel
                                </button>
                                @endif
                            </div>
                        </div>

                        {{-- File entities --}}
                        @forelse($this->page?->files ?? [] as $docFile)
                        <div class="border border-gray-200 dark:border-gray-700 rounded-xl overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 bg-gray-50 dark:bg-gray-800">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $docFile->name }}</span>
                                    <span class="text-xs bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-400 px-1.5 py-0.5 rounded">
                                        .{{ $docFile->extension }}
                                    </span>
                                    @if($docFile->is_text)
                                    <span class="text-xs bg-blue-100 text-blue-600 dark:bg-blue-900 dark:text-blue-300 px-1.5 py-0.5 rounded">
                                        text
                                    </span>
                                    @endif
                                </div>
                                <button wire:click="prepareNewVersion({{ $docFile->id }})"
                                        class="text-xs text-indigo-600 hover:text-indigo-800 dark:hover:text-indigo-400 transition-colors">
                                    + Version
                                </button>
                            </div>

                            <div class="divide-y divide-gray-100 dark:divide-gray-700">
                                @foreach($docFile->fileVersions as $fv)
                                <div class="flex items-center justify-between px-4 py-2 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-semibold text-indigo-600 dark:text-indigo-400">v{{ $fv->version_number }}</span>
                                        <span class="text-gray-600 dark:text-gray-400 truncate max-w-[120px]">{{ $fv->original_name }}</span>
                                        <span class="text-gray-400">{{ $fv->humanSize() }}</span>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        @if($docFile->is_text && ! $loop->last)
                                        @php
                                            $nextFv = $docFile->fileVersions[$loop->index - 1] ?? null;
                                        @endphp
                                        @if($nextFv)
                                        <button wire:click="openDiff({{ $docFile->id }}, {{ $fv->id }}, {{ $nextFv->id }})"
                                                class="text-blue-500 hover:text-blue-700 dark:hover:text-blue-300 transition-colors">
                                            Diff
                                        </button>
                                        @endif
                                        @endif
                                        <a href="{{ $fv->downloadUrl() }}" target="_blank"
                                           class="text-gray-400 hover:text-indigo-600 transition-colors">
                                            ↓ Download
                                        </a>
                                        <button wire:click="deleteFileVersion({{ $fv->id }})"
                                                wire:confirm="Delete this file version?"
                                                class="text-red-400 hover:text-red-600 transition-colors">
                                            ✕
                                        </button>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @empty
                        <p class="text-gray-400 text-xs text-center py-4">No files yet.</p>
                        @endforelse
                    </div>
                    @endif
                @endif

            </div>{{-- end tab content --}}

            {{-- Footer --}}
            <div class="px-5 py-2.5 border-t border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-xs text-gray-400 text-right">
                DevTools DocSystem &mdash; dev only
            </div>

        </div>{{-- end card --}}
    </div>{{-- end modal --}}

</div>
