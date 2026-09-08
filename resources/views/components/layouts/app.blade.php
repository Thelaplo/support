<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Support Tickets - XEFI' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body class="bg-gray-100 text-gray-900 font-sans min-h-screen">
    <header class="bg-slate-900 text-white shadow">
        <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
            <a href="/" class="font-bold text-lg tracking-wide text-blue-400">Support Desk</a>
            <div class="flex items-center gap-4 text-sm">
                @auth
                    <span>{{ auth()->user()->name }}</span>
                    <form method="POST" action="/logout" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-400 hover:text-white">Déconnexion</button>
                    </form>
                @endauth
            </div>
        </div>
    </header>

    <main class="max-w-6xl mx-auto px-4 py-8">
        {{ $slot }}
    </main>

    @livewireScripts
</body>
</html>
