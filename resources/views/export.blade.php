@extends('layouts.admin')

@section('title', 'Ekspor Data')
@section('page_title', 'Ekspor Data')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Ekspor</li>
    </ol>
@stop

@section('content')
<div class="row">
    <div class="col-md-8">
        <!-- Export Options -->
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-file-export mr-1"></i> Ekspor Data Kejadian
                </h3>
            </div>
            <div class="card-body">
                <!-- Filter Section -->
                <div class="mb-4 p-3 bg-light rounded">
                    <h6 class="text-muted mb-3">
                        <i class="fas fa-filter mr-1"></i> Filter Rentang Tanggal (Opsional)
                    </h6>
                    <form method="GET" action="{{ route('export.index') }}">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group mb-0">
                                    <label for="date_from">Tanggal Mulai</label>
                                    <input type="date" name="date_from" id="date_from"
                                           class="form-control"
                                           value="{{ request('date_from') }}">
                                </div>
                            </div>
                            <div class="col-md-5">
                                <div class="form-group mb-0">
                                    <label for="date_to">Tanggal Akhir</label>
                                    <input type="date" name="date_to" id="date_to"
                                           class="form-control"
                                           value="{{ request('date_to') }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group mb-0">
                                    <label>&nbsp;</label>
                                    <button type="submit" class="btn btn-outline-dark btn-block">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Export Buttons -->
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-danger mb-3 mb-md-0">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-file-pdf fa-3x text-danger mb-3"></i>
                                <h5>Ekspor PDF</h5>
                                <p class="text-muted small">Unduh data kejadian dalam format PDF.</p>
                                <form method="POST" action="{{ route('export.pdf') }}">
                                    @csrf
                                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                    <button type="submit" class="btn btn-danger btn-block">
                                        <i class="fas fa-download mr-1"></i> Ekspor PDF
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-body text-center py-4">
                                <i class="fas fa-file-csv fa-3x text-success mb-3"></i>
                                <h5>Ekspor CSV</h5>
                                <p class="text-muted small">Unduh data kejadian dalam format CSV.</p>
                                <form method="POST" action="{{ route('export.csv') }}">
                                    @csrf
                                    <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                    <input type="hidden" name="date_to" value="{{ request('date_to') }}">
                                    <button type="submit" class="btn btn-success btn-block">
                                        <i class="fas fa-download mr-1"></i> Ekspor CSV
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <!-- Info Card -->
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle mr-1"></i> Informasi
                </h3>
            </div>
            <div class="card-body">
                <h6 class="text-muted">Format Ekspor</h6>
                <ul class="text-muted small pl-3">
                    <li class="mb-2">
                        <strong>PDF:</strong> Format dokumen siap cetak dengan tabel kejadian.
                    </li>
                    <li class="mb-2">
                        <strong>CSV:</strong> Format data mentah yang dapat dibuka di Excel atau Google Sheets.
                    </li>
                </ul>
                <hr>
                <h6 class="text-muted">Rentang Tanggal</h6>
                <p class="text-muted small">
                    Jika rentang tanggal dikosongkan, seluruh data akan diekspor.
                    Gunakan filter tanggal di atas untuk membatasi data yang diekspor.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
