<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'XEFI Support Desk' }}</title>
    
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        xefi: {
                            red: '#E2001A',
                            darkred: '#B80015',
                            black: '#111827',
                            gray: '#1F2937'
                        }
                    }
                }
            }
        }
    </script>
    @livewireStyles
</head>
<body class="h-full font-sans text-slate-800 antialiased flex flex-col">
    <!-- Header Corporate XEFI -->
    <header class="bg-xefi-black text-white border-b-4 border-xefi-red shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-3.5 flex justify-between items-center">
            <div class="flex items-center gap-8">
                <a href="/tickets" class="flex items-center gap-3 group">
                    <!-- Logo badge XEFI style -->
                    <span class="bg-xefi-red text-white font-extrabold px-3 py-1 rounded text-xl tracking-wider shadow">
                        XEFI
                    </span>
                    <span class="text-sm font-semibold tracking-wide text-slate-300 group-hover:text-white transition">
                        Support Desk
                    </span>
                </a>
                
                <nav class="hidden md:flex items-center gap-1">
                    <a href="/tickets" class="px-3 py-2 rounded-md text-sm font-medium text-slate-200 hover:bg-slate-800 transition">
                        Tableau de bord
                    </a>
                </nav>
            </div>

            <div class="flex items-center gap-4">
                <a href="/tickets/create" 
                   class="inline-flex items-center gap-2 bg-xefi-red hover:bg-xefi-darkred text-white text-xs font-semibold uppercase tracking-wider px-4 py-2 rounded shadow-sm transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Nouveau ticket
                </a>

                @auth
                    <div class="hidden sm:flex items-center gap-3 border-l border-slate-700 pl-4 text-xs">
                        <span class="w-7 h-7 rounded-full bg-slate-800 border border-slate-600 flex items-center justify-center font-bold text-slate-300">
                            {{ substr(auth()->user()->name, 0, 1) }}
                        </span>
                        <span class="text-slate-300 font-medium">{{ auth()->user()->name }}</span>
                    </div>
                @endauth
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Footer Corporate -->
    <footer class="bg-white border-t border-slate-200 py-4 text-center text-xs text-slate-500">
        <div class="max-w-7xl mx-auto px-4">
            &copy; {{ date('Y') }} XEFI Informatique & Services — Plateforme Support & Assistance
        </div>
    </footer>

    @livewireScripts
</body>
</html>
