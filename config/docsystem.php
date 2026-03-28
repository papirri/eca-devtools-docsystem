<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | DocSystem Environment Guard
    |--------------------------------------------------------------------------
    | This package is designed for development environments only.
    | It will automatically disable itself in production.
    */
    'enabled' => env('DOCSYSTEM_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | File Storage
    |--------------------------------------------------------------------------
    | Directory inside storage/app/public where doc files are stored.
    */
    'storage_path' => env('DOCSYSTEM_STORAGE_PATH', 'doc-system'),

    /*
    |--------------------------------------------------------------------------
    | Allowed File Types
    |--------------------------------------------------------------------------
    | MIME types allowed for upload.
    */
    'allowed_mimes' => [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp',
        'image/svg+xml',
        'application/pdf',
        'text/plain',
        'text/markdown',
        'text/csv',
        'application/json',
        'application/zip',
        'application/x-zip-compressed',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ],

    /*
    |--------------------------------------------------------------------------
    | Max File Size (KB)
    |--------------------------------------------------------------------------
    */
    'max_file_size' => env('DOCSYSTEM_MAX_FILE_SIZE', 10240), // 10 MB

    /*
    |--------------------------------------------------------------------------
    | Text-based extensions (for diff comparison)
    |--------------------------------------------------------------------------
    */
    'text_extensions' => [
        'txt', 'md', 'json', 'xml', 'csv',
        'php', 'js', 'ts', 'css', 'html',
        'blade', 'env', 'yaml', 'yml',
        'sh', 'sql', 'log',
    ],

    /*
    |--------------------------------------------------------------------------
    | Note types available
    |--------------------------------------------------------------------------
    */
    'note_types' => [
        'avance'   => 'Avance',
        'pregunta' => 'Pregunta',
        'error'    => 'Error',
        'nota'     => 'Nota',
    ],

    /*
    |--------------------------------------------------------------------------
    | Timeline event types
    |--------------------------------------------------------------------------
    */
    'event_types' => [
        'created',
        'updated',
        'note_added',
        'file_uploaded',
        'file_updated',
        'file_deleted',
    ],
];
