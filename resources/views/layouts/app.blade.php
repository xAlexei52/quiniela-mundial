<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quiniela Mageova') · {{ config('app.name', 'Mundial 2026') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
@php
    $nav = [
        ['home',          'Resumen',       '🏆'],
        ['participantes', 'Participantes', '👥'],
        ['grupos',        'Grupos',        '📊'],
        ['bracket',       'Eliminatorias', '🗺️'],
        ['admin.dashboard','Admin',        '⚙️'],
    ];
@endphp
<div class="app">
    <aside class="sidebar">
        <div class="brand">
            <div class="logo">⚽</div>
            <div>
                <b>Quiniela Mageova</b>
                <small>Mundial 2026</small>
            </div>
        </div>

        @foreach ($nav as [$route, $label, $ico])
            <a href="{{ $route === 'admin.dashboard' ? route('admin.login') : route($route) }}"
               class="nav-link {{ request()->routeIs($route) || ($route==='admin.dashboard' && request()->routeIs('admin.*')) ? 'active' : '' }}">
                <span class="ico">{{ $ico }}</span> {{ $label }}
            </a>
        @endforeach

        <div class="sidebar-foot">
            Bote ${{ number_format((int) config('quiniela.prize.pool')) }} · reparto top 3<br>
            Hecho con ⚽ para los compas
        </div>
    </aside>

    <main class="main">
        @if (session('status'))
            <div class="notice" style="margin-bottom:1rem">{{ session('status') }}</div>
        @endif
        @if (session('error'))
            <div class="error-box" style="margin-bottom:1rem">{{ session('error') }}</div>
        @endif

        @yield('content')
    </main>
</div>

<nav class="bottom-nav">
    @foreach ($nav as [$route, $label, $ico])
        <a href="{{ $route === 'admin.dashboard' ? route('admin.login') : route($route) }}"
           class="bottom-link {{ request()->routeIs($route) || ($route==='admin.dashboard' && request()->routeIs('admin.*')) ? 'active' : '' }}">
            <span class="ico">{{ $ico }}</span>
            <span class="lbl">{{ $label }}</span>
        </a>
    @endforeach
</nav>
</body>
</html>
