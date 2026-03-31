<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Devtools\DocSystem\Livewire\DocSystemAdmin;

Route::middleware(['web', 'auth'])
    ->get(config('docsystem.admin_route', 'docsystem/admin'), DocSystemAdmin::class)
    ->name('docsystem.admin');
