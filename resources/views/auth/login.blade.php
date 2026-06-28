@extends('layouts.admin')

@section('title', 'Masuk')

@section('styles')
<style>
    .login-page {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
        background: linear-gradient(135deg, #343a40 0%, #212529 100%);
        margin: 0;
        padding: 0;
    }
    .login-card {
        width: 100%;
        max-width: 420px;
    }
    .login-card .card {
        border: none;
        border-radius: 0.75rem;
        box-shadow: 0 0.5rem 2rem rgba(0, 0, 0, 0.35);
    }
    .login-card .card-header {
        background: #343a40;
        border-bottom: none;
        border-radius: 0.75rem 0.75rem 0 0 !important;
        padding: 2rem 1.5rem 1rem;
        text-align: center;
    }
    .login-card .card-body {
        padding: 2rem;
    }
    .login-card .card-footer {
        background: transparent;
        border-top: 1px solid #dee2e6;
        text-align: center;
        padding: 1rem 2rem;
    }
    .brand-icon {
        font-size: 3rem;
        color: #fff;
    }
    .brand-text-login {
        font-size: 1.75rem;
        font-weight: 300;
        color: #fff;
        margin-top: 0.25rem;
    }
    .brand-subtitle {
        font-size: 0.85rem;
        color: rgba(255,255,255,0.6);
        margin-top: 0.25rem;
    }
    /* Override AdminLTE body classes for login */
    body.hold-transition { margin-left: 0 !important; }
    .wrapper { margin-left: 0 !important; }
    .main-sidebar, .main-header, .main-footer { display: none !important; }
    .content-wrapper { margin-left: 0 !important; }
</style>
@endsection

@section('content')
<div class="login-card mx-auto">
    <div class="card">
        <div class="card-header">
            <i class="fas fa-shield-alt brand-icon"></i>
            <div class="brand-text-login">PantauKu</div>
            <div class="brand-subtitle">Dashboard Pemantauan Perangkat</div>
        </div>

        <div class="card-body">
            <h5 class="text-center mb-4">Masuk ke Akun</h5>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                        <input type="email"
                               name="email"
                               id="email"
                               class="form-control @error('email') is-invalid @enderror"
                               value="{{ old('email') }}"
                               placeholder="nama@contoh.com"
                               required
                               autofocus>
                    </div>
                    @error('email')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="password">Kata Sandi</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password"
                               name="password"
                               id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               placeholder="Kata sandi Anda"
                               required>
                    </div>
                    @error('password')
                        <span class="text-danger small">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-group">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" name="remember" id="remember" class="custom-control-input" {{ old('remember') ? 'checked' : '' }}>
                        <label class="custom-control-label" for="remember">Ingat Saya</label>
                    </div>
                </div>

                <button type="submit" class="btn btn-dark btn-block btn-lg">
                    <i class="fas fa-sign-in-alt mr-2"></i> Masuk
                </button>
            </form>
        </div>

        <div class="card-footer">
            <small class="text-muted">&copy; {{ date('Y') }} PantauKu. Seluruh hak cipta dilindungi.</small>
        </div>
    </div>
</div>
@endsection
