<!DOCTYPE html>
<html lang="ar">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('page_name')</title>


    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        input {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        input:focus {
            transform: scale(1.02);
        }
    </style>
</head>

<body class="font-sans text-gray-800 antialiased">
    <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100">
        <h1
            style="
    font-weight: 900;
    font-size: 56px; 
    background: linear-gradient(to right, #51141b, #8d5450);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    letter-spacing: -2px;
">
            Flow
        </h1>

        <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white shadow-md overflow-hidden sm:rounded-lg">
            {{ $slot }}
        </div>
    </div>
</body>

</html>
