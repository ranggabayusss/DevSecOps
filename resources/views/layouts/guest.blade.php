<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Unknown Rider</title>
    @vite(['resources/css/app.css'])
</head>
<body class="flex min-h-screen flex-col bg-gray-950 text-gray-100 antialiased">

    <main class="flex flex-1 items-center justify-center px-4 py-10">
        <div class="w-full max-w-md">

            <div class="mb-8 text-center">
                <a href="{{ url('/') }}"
                   class="text-xl font-bold uppercase tracking-[0.25em] text-orange-500">
                    Unknown Rider
                </a>
                <p class="mt-2 text-xs uppercase tracking-widest text-gray-500">
                    Komunitas &amp; Touring
                </p>
            </div>

            @yield('content')

            <p class="mt-8 text-center text-xs text-gray-600">
                &copy; {{ date('Y') }} Unknown Rider
            </p>
        </div>
    </main>

</body>
</html>
