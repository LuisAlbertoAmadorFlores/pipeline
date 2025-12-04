@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Cambiar contraseña</h1>

        <form method="POST" action="{{ route('password.force_update') }}">
            @csrf

            <div class="form-group">
                <label>Nueva contraseña</label>
                <input name="password" type="password" class="form-control" required minlength="8">
            </div>

            <div class="form-group">
                <label>Confirmar contraseña</label>
                <input name="password_confirmation" type="password" class="form-control" required minlength="8">
            </div>

            <button class="btn btn-primary">Cambiar contraseña</button>
        </form>
    </div>
@endsection
