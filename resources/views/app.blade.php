<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <title inertia>{{ config('app.name', 'Laravel') }}</title>
    <script src="https://www.gstatic.com/cv/js/sender/v1/cast_sender.js?loadCastFramework=1"></script>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" />
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

 
    
    <!-- Inertia/Vite Assets -->
    @vite('resources/js/app.js')
 @routes
    <!-- Inertia Head -->
    @inertiaHead

<!-- En tu layout (por ejemplo, layouts/app.blade.php), después de tu JS -->


    
</head>
<body class="font-sans antialiased">
    @inertia
 
</body>
</html>
