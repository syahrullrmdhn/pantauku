@extends('layouts.admin')

@section('title', 'Pengaturan')
@section('page_title', 'Pengaturan')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Pengaturan</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-6">
        <!-- Telegram Settings -->
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-telegram-plane mr-1"></i> Notifikasi Telegram
                </h3>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('settings.update') }}">
                    @csrf
                    @method('PUT')

                    <div class="form-group">
                        <label for="telegram_bot_token">
                            <i class="fas fa-robot mr-1"></i> Token Bot Telegram
                        </label>
                        <input type="text"
                               name="telegram_bot_token"
                               id="telegram_bot_token"
                               class="form-control @error('telegram_bot_token') is-invalid @enderror"
                               placeholder="Masukkan token bot Telegram"
                               value="{{ old('telegram_bot_token', $telegramBotToken ?? '') }}">
                        @error('telegram_bot_token')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            Dapatkan token dari @BotFather di Telegram.
                        </small>
                    </div>

                    <div class="form-group">
                        <label for="telegram_chat_id">
                            <i class="fas fa-comments mr-1"></i> ID Chat Telegram
                        </label>
                        <input type="text"
                               name="telegram_chat_id"
                               id="telegram_chat_id"
                               class="form-control @error('telegram_chat_id') is-invalid @enderror"
                               placeholder="Masukkan ID chat Telegram"
                               value="{{ old('telegram_chat_id', $telegramChatId ?? '') }}">
                        @error('telegram_chat_id')
                            <span class="text-danger small">{{ $message }}</span>
                        @enderror
                        <small class="form-text text-muted">
                            ID chat untuk menerima notifikasi (contoh: 123456789).
                        </small>
                    </div>

                    <button type="submit" class="btn btn-dark">
                        <i class="fas fa-save mr-1"></i> Simpan Pengaturan
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <!-- Info Card -->
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i> Informasi
                </h3>
            </div>
            <div class="card-body">
                <h5 class="text-muted">PantauKu</h5>
                <p class="text-muted">
                    Dashboard Pemantauan Perangkat versi 1.0.0. Aplikasi ini memantau kejadian
                    dari perangkat terpasang, termasuk pembukaan aplikasi dan akses browser.
                </p>
                <hr>
                <h6 class="text-muted">Notifikasi Telegram</h6>
                <p class="text-muted">
                    Dengan mengaktifkan notifikasi Telegram, Anda akan menerima pemberitahuan
                    setiap kali terdeteksi kejadian mencurigakan pada perangkat yang dipantau.
                    Pastikan bot Telegram sudah dibuat dan ID chat sudah sesuai.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
