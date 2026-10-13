<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<title>{{ isset($title) ? $title.' - '.config('app.name') : config('app.name') }}</title>

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

{{-- Apply the saved desktop sidebar state before the first paint to avoid a flash. --}}
<script>
    try {
        if (localStorage.getItem('penna.sidebar') === 'hidden') {
            document.documentElement.classList.add('sidebar-hidden');
        }
    } catch (e) {}
</script>

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
