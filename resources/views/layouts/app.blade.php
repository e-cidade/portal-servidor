<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Validação de Documento' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">
<!-- Cabeçalho -->
<header class="bg-green-800 text-white shadow">
    <div class="max-w-4xl mx-auto px-4 py-4">
        <h1 class="text-xl font-bold text-center">{{ $instituicao->nomeinst }} </h1>
        <h3 class="text-xl font-bold text-center">Validação de Contracheque</h3>
    </div>
</header>

<!-- Conteúdo -->
<main class="flex-1 flex items-center justify-center p-6">
    <div class="w-full max-w-lg">
        @yield('content')
    </div>
</main>

<!-- Rodapé -->
<footer class="bg-gray-200 text-center text-sm text-gray-600 py-4">
    © {{ date('Y') }} {{ $instituicao->nomeinst }} - Portal do Servidor
</footer>
</body>
</html>
