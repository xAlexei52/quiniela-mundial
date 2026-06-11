@extends('layouts.app')
@section('title', 'Admin')

@section('content')
<div class="page-head">
    <h1>Panel de administración</h1>
    <div class="sub">Ingresa el PIN para gestionar participantes y equipos</div>
</div>

<div class="card" style="max-width:420px">
    <h3>Acceso</h3>
    <form method="POST" action="{{ route('admin.authenticate') }}">
        @csrf
        <label class="muted" style="font-size:.8rem">PIN</label>
        <input type="password" name="pin" autofocus autocomplete="off"
               style="width:100%; margin:.4rem 0 .2rem" placeholder="••••">
        @error('pin')
            <div class="error-box" style="margin:.5rem 0">{{ $message }}</div>
        @enderror
        <button class="btn primary" style="margin-top:.7rem; width:100%">Entrar</button>
    </form>
</div>
@endsection
