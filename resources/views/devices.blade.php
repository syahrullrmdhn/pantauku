@extends('layouts.admin')

@section('title', 'Perangkat Terhubung')
@section('page_title', 'Perangkat Terhubung')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Perangkat</li>
    </ol>
@stop

@section('content')
<!-- Stat Cards -->
<div class="row">
    <div class="col-lg-4 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalDevices }}</h3>
                <p>Total Perangkat</p>
            </div>
            <div class="icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $onlineDevices }}</h3>
                <p>Online (10 menit terakhir)</p>
            </div>
            <div class="icon">
                <i class="fas fa-wifi"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-4 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $totalDevices - $onlineDevices }}</h3>
                <p>Offline</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
</div>

<!-- Device List -->
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Daftar Perangkat
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                @if($devices->isEmpty())
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-mobile-alt fa-3x mb-3 d-block"></i>
                        <p>Belum ada perangkat terhubung.</p>
                        <small>Install Pan Browser APK di HP dan data akan muncul di sini.</small>
                    </div>
                @else
                <table class="table table-hover table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px">#</th>
                            <th>Nama Perangkat</th>
                            <th>Device ID</th>
                            <th>Event</th>
                            <th>Lokasi Terakhir</th>
                            <th>Terakhir Terlihat</th>
                            <th>Status</th>
                            <th style="width: 80px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($devices as $i => $device)
                        <tr>
                            <td>{{ $i + 1 }}</td>
                            <td>
                                <span class="device-name-display" id="name-display-{{ $device->device_id }}">
                                    <strong>{{ $device->device_name ?: 'Tanpa Nama' }}</strong>
                                </span>
                                <form method="POST" action="{{ route('devices.update-name') }}"
                                      class="device-name-form d-none" id="name-form-{{ $device->device_id }}">
                                    @csrf
                                    <input type="hidden" name="device_id" value="{{ $device->device_id }}">
                                    <div class="input-group input-group-sm" style="max-width: 200px;">
                                        <input type="text" name="device_name" class="form-control"
                                               value="{{ $device->device_name }}" maxlength="100" required>
                                        <div class="input-group-append">
                                            <button type="submit" class="btn btn-primary"><i class="fas fa-check"></i></button>
                                            <button type="button" class="btn btn-secondary cancel-edit"
                                                    data-device-id="{{ $device->device_id }}"><i class="fas fa-times"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </td>
                            <td><code style="font-size: 11px;">{{ substr($device->device_id, 0, 16) }}...</code></td>
                            <td>{{ $device->event_count }}</td>
                            <td>
                                @if($device->last_location)
                                    <a href="https://www.google.com/maps?q={{ $device->last_location->latitude }},{{ $device->last_location->longitude }}"
                                       target="_blank" rel="noopener" class="text-primary">
                                        <i class="fas fa-map-marker-alt mr-1"></i>
                                        {{ number_format($device->last_location->latitude, 4) }},
                                        {{ number_format($device->last_location->longitude, 4) }}
                                    </a>
                                    <br><small class="text-muted">{{ $device->last_location->occurred_at->diffForHumans() }}</small>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $device->last_seen_at ? $device->last_seen_at->diffForHumans() : '—' }}
                                <br><small class="text-muted">{{ $device->last_seen_at ? $device->last_seen_at->format('d M Y H:i') : '' }}</small>
                            </td>
                            <td>
                                @if($device->last_seen_at && $device->last_seen_at->diffInMinutes() < 10)
                                    <span class="badge badge-success">Online</span>
                                @else
                                    <span class="badge badge-secondary">Offline</span>
                                @endif
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary edit-name-btn"
                                        data-device-id="{{ $device->device_id }}"
                                        title="Edit nama">
                                    <i class="fas fa-edit"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>
@stop

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit name button
    document.querySelectorAll('.edit-name-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var deviceId = this.getAttribute('data-device-id');
            document.getElementById('name-display-' + deviceId).classList.add('d-none');
            document.getElementById('name-form-' + deviceId).classList.remove('d-none');
            document.getElementById('name-form-' + deviceId).querySelector('input').focus();
        });
    });

    // Cancel edit
    document.querySelectorAll('.cancel-edit').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var deviceId = this.getAttribute('data-device-id');
            document.getElementById('name-display-' + deviceId).classList.remove('d-none');
            document.getElementById('name-form-' + deviceId).classList.add('d-none');
        });
    });
});
</script>
@stop
