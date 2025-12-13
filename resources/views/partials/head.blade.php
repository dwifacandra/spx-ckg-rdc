<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>
    @isset($title)
    {{ $title }} | {{ config('app.name') }}
    @else
    {{ config('app.name') }}
    @endisset
</title>

<link rel="icon" href="/app-logo.webp" sizes="any">
<link rel="icon" href="/app-logo.webp" type="image/svg+xml">
<link rel="apple-touch-icon" href="/app-logo.webp">

<link rel="preconnect" href="https://fonts.bunny.net">
<link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />

@vite(['resources/css/app.css', 'resources/js/app.js'])
@fluxAppearance
