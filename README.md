# DevTools DocSystem

> **Development-only** in-app documentation and activity tracking system, per URL/page.

![DocSystem floating button](https://via.placeholder.com/800x100/6366f1/ffffff?text=DocSystem+%E2%80%94+floating+dev+panel)

---

## Features

| Feature | Details |
|---|---|
| 📄 Page docs | Each URL gets its own doc record, auto-detected |
| 🔖 Versioning | Multiple versions per page (title, description, author) |
| 📅 Timeline | Full activity log with metadata |
| 📝 Notes | Types: `avance`, `pregunta`, `error`, `nota` |
| 📎 Files | Upload files with version history |
| 🔍 Diff viewer | Line-by-line diff for text files (LCS algorithm) |
| 🛡️ Dev only | Auto-disabled in `production` environment |
| 🧹 Purge | One-command full cleanup |

---

## Requirements

- PHP 8.3+
- Laravel 11 / 12 / 13
- Livewire v3 or v4
- Tailwind CSS (already in your project)

---

## Installation

### 1. Require the package

```bash
composer require eca-devtools/docsystem
```

### 2. Publish and run migrations

```bash
php artisan vendor:publish --tag=docsystem-migrations
php artisan migrate
```

### 3. (Optional) Publish config

```bash
php artisan vendor:publish --tag=docsystem-config
```

### 4. Add the component to your layout

Inside your main Blade layout, before `</body>`:

```blade
@auth
    <livewire:docsystem-panel />
@endauth
```

> The component already checks `Auth::check()` and `app()->environment('production')` internally, so it's double-guarded.

---

## Usage

1. Visit any page while authenticated.
2. A floating indigo button appears in the bottom-right corner.
3. Click it to open the panel.

### Tabs

| Tab | Actions |
|---|---|
| **Documentation** | Create / edit version records with title, version number & description |
| **Timeline** | Read-only activity log ordered by newest first |
| **Notes** | Add typed notes (`avance`, `pregunta`, `error`, `nota`) |
| **Files** | Upload files, add new versions, download, or compare (diff) text files |

### File diff

For text-based files (`.txt`, `.md`, `.json`, `.php`, `.js`, etc.) a **Diff** button appears between versions. It renders a side-by-side line-level comparison using a built-in LCS algorithm — no external library required.

---

## Configuration (`config/docsystem.php`)

```php
return [
    'enabled'          => env('DOCSYSTEM_ENABLED', true),
    'storage_path'     => env('DOCSYSTEM_STORAGE_PATH', 'doc-system'),
    'max_file_size'    => env('DOCSYSTEM_MAX_FILE_SIZE', 10240), // KB
    'text_extensions'  => ['txt','md','json','php','js','ts', ...],
    'note_types'       => ['avance','pregunta','error','nota'],
    'event_types'      => ['created','updated','note_added','file_uploaded',
                           'file_updated','file_deleted'],
];
```

---

## Commands

### Purge (cleanup)

```bash
php artisan docsystem:purge
```

This command:
- Only runs in `local` / non-production environments
- Asks for explicit confirmation
- Drops all package database tables (`doc_pages`, `doc_versions`, `doc_events`, `doc_notes`, `doc_files`, `doc_file_versions`)
- Deletes all files from `storage/app/public/doc-system`

---

## Database tables

| Table | Description |
|---|---|
| `doc_pages` | One record per unique URL path |
| `doc_versions` | Version history per page |
| `doc_events` | Timeline / activity log |
| `doc_notes` | Typed notes per page |
| `doc_files` | Logical file entities (groups versions) |
| `doc_file_versions` | Individual uploaded file versions |

---

## Screenshots (placeholders)

**Floating button**
![Floating button](https://via.placeholder.com/400x300/6366f1/ffffff?text=Floating+button)

**Documentation tab**
![Documentation tab](https://via.placeholder.com/800x500/f8fafc/1e293b?text=Documentation+tab)

**Timeline tab**
![Timeline tab](https://via.placeholder.com/800x500/f8fafc/1e293b?text=Timeline+tab)

**Notes tab**
![Notes tab](https://via.placeholder.com/800x500/f8fafc/1e293b?text=Notes+tab)

**Files + Diff tab**
![Files tab](https://via.placeholder.com/800x500/f8fafc/1e293b?text=Files+%2B+Diff+viewer)

---

## Production safety

The package automatically refuses to boot if:
```php
app()->environment('production') === true
```

No routes, no Livewire components, no commands will be registered in production. Zero overhead.

---

## Uninstall

```bash
# 1. Run purge to clean database tables and files
php artisan docsystem:purge

# 2. Remove the package
composer remove eca-devtools/docsystem

# 3. Remove the component tag from your layout
# (delete the <livewire:docsystem-panel /> line)

# 4. (Optional) Remove published config
rm config/docsystem.php
```

---

## License

MIT
