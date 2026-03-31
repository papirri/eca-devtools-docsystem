<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DocSystem Admin — {{ config('app.name', 'Laravel') }}</title>
    {{-- Tailwind Play CDN — acceptable for this dev-only tool --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'media',
        }
    </script>
    @livewireStyles
</head>
<body class="h-full antialiased bg-gray-50 dark:bg-gray-950">
    {{ $slot }}
    @livewireScripts
</body>
</html>
