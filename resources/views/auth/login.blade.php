<!DOCTYPE html>
<html lang="fr" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion — XEFI Support Desk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        xefi: { red: '#E2001A', darkred: '#B80015', black: '#111827' }
                    }
                }
            }
        }
    </script>
</head>
<body class="h-full flex items-center justify-center font-sans antialiased">
    <div class="max-w-md w-full mx-4 bg-white p-8 rounded-2xl shadow-sm border border-slate-200">
        <div class="text-center mb-8">
            <span class="inline-block bg-xefi-red text-white font-extrabold px-4 py-1.5 rounded text-2xl tracking-wider shadow mb-3">
                XEFI
            </span>
            <h1 class="text-xl font-bold text-slate-900">Connexion au Support Desk</h1>
            <p class="text-xs text-slate-500 mt-1">Veuillez vous identifier pour accéder à vos tickets.</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-xefi-red text-xs rounded-lg">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="/login" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Adresse Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full border-slate-300 rounded-lg text-sm shadow-sm focus:border-xefi-red focus:ring-xefi-red p-2.5 border"
                       placeholder="nom@xefi.fr">
            </div>

            <div>
                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">Mot de passe</label>
                <input type="password" name="password" required
                       class="w-full border-slate-300 rounded-lg text-sm shadow-sm focus:border-xefi-red focus:ring-xefi-red p-2.5 border"
                       placeholder="••••••••">
            </div>

            <button type="submit" 
                    class="w-full bg-xefi-red hover:bg-xefi-darkred text-white text-sm font-semibold py-2.5 rounded-lg shadow-sm transition">
                Se connecter
            </button>
        </form>
    </div>
</body>
</html>
