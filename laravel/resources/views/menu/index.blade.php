<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Menú</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body>
    <main class="container">
        <h1>Menú del restaurante</h1>
        @if ($categoria)
            <p>Filtrando por categoría: <strong>{{ $categoria }}</strong></p>
        @endif

        <section class="menu-grid">
            @forelse ($items as $item)
                <article class="menu-card">
                    <img src="{{ $item->imagen ? asset('storage/'.$item->imagen) : asset('img/placeholder.png') }}" alt="{{ $item->nombre }}" width="160" height="120">
                    <h2>{{ $item->nombre }}</h2>
                    <p>{{ $item->descripcion }}</p>
                    <p><strong>$ {{ number_format($item->precio, 0, ',', '.') }}</strong></p>
                    <button class="agregar-carrito" data-id="{{ $item->id }}">Agregar al carrito</button>
                </article>
            @empty
                <p>No hay productos disponibles.</p>
            @endforelse
        </section>
    </main>

    <script src="{{ asset('js/enviar.js') }}" defer></script>
</body>
</html>
