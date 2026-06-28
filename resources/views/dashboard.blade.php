@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item active">Dashboard</li>
    </ol>
@stop

@section('content')
<!-- Stat Cards -->
<div class="row">
    <!-- Total Events -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalEvents ?? 0 }}</h3>
                <p>Total Kejadian</p>
            </div>
            <div class="icon">
                <i class="fas fa-chart-bar"></i>
            </div>
        </div>
    </div>

    <!-- App Opens -->
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $appOpens ?? 0 }}</h3>
                <p>Aplikasi Dibuka</p>
            </div>
            <div class="icon">
                <i class="fas fa-mobile-alt"></i>
            </div>
        </div>
    </div>

    <!-- Browser Accesses -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $browserAccesses ?? 0 }}</h3>
                <p>Browser</p>
            </div>
            <div class="icon">
                <i class="fas fa-globe"></i>
            </div>
        </div>
    </div>

    <!-- Download APK -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3><i class="fas fa-download"></i></h3>
                <p>Unduh APK</p>
            </div>
            <a href="/pan-browser.apk" class="small-box-footer" download>
                Download <i class="fas fa-arrow-circle-down ml-1"></i>
            </a>
        </div>
    </div>

    <!-- Suspicious -->
    <div class="col-lg-2 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $suspiciousCount ?? 0 }}</h3>
                <p>Mencurigakan</p>
            </div>
            <div class="icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filter Form -->
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-filter mr-1"></i> Filter Data
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('dashboard') }}">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_from">Tanggal Mulai</label>
                                <input type="date" name="date_from" id="date_from"
                                       class="form-control"
                                       value="{{ request('date_from') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date_to">Tanggal Akhir</label>
                                <input type="date" name="date_to" id="date_to"
                                       class="form-control"
                                       value="{{ request('date_to') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="type">Tipe Kejadian</label>
                                <select name="type" id="type" class="form-control">
                                    <option value="">Semua</option>
                                    <option value="app_open" {{ request('type') == 'app_open' ? 'selected' : '' }}>Buka Aplikasi</option>
                                    <option value="browser_access" {{ request('type') == 'browser_access' ? 'selected' : '' }}>Akses Browser</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="suspicious" id="suspicious"
                                           class="custom-control-input" value="1"
                                           {{ request('suspicious') ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="suspicious">Hanya Mencurigakan</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <button type="submit" class="btn btn-dark">
                                <i class="fas fa-search mr-1"></i> Terapkan Filter
                            </button>
                            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-redo mr-1"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Events Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-list mr-1"></i> Daftar Kejadian
                </h3>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th>Waktu</th>
                            <th>Tipe</th>
                            <th>Nilai</th>
                            <th>Mencurigakan</th>
                            <th>ID Perangkat</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($events ?? [] as $event)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($event->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                @if($event->type == 'app_open')
                                    <span class="badge badge-success">Buka Aplikasi</span>
                                @elseif($event->type == 'browser_access')
                                    <span class="badge badge-warning">Akses Browser</span>
                                @else
                                    <span class="badge badge-secondary">{{ $event->type }}</span>
                                @endif
                            </td>
                            <td>{{ $event->value ?? '-' }}</td>
                            <td>
                                @if($event->is_suspicious)
                                    <span class="badge badge-danger">Ya</span>
                                @else
                                    <span class="badge badge-success">Tidak</span>
                                @endif
                            </td>
                            <td><code>{{ $event->device_id ?? '-' }}</code></td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x d-block mb-2"></i>
                                Tidak ada data kejadian.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($events) && $events->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $events->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
