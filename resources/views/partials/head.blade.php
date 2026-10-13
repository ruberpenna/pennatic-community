<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Flux restores the collapsed sidebar only after its script loads; hint it before the first paint to avoid a jump. --}}
<script>
    try {
        if (localStorage.getItem('flux-sidebar-collapsed-desktop') === 'true') {
            document.documentElement.classList.add('sidebar-collapsed');
        }
    } catch (e) {}
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
