@extends('layouts.admin')

@section('title', 'Blacklist Domain')
@section('page_title', 'Blacklist Domain')

@section('breadcrumb')
    <ol class="breadcrumb float-sm-right">
        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
        <li class="breadcrumb-item active">Blacklist Domain</li>
    </ol>
@stop

@section('content')
<!-- Add Domain Form -->
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle mr-1"></i> Tambah Domain
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                    </button>
                </div>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('blacklist.store') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="domain">Domain</label>
                                <input type="text" name="domain" id="domain"
                                       class="form-control @error('domain') is-invalid @enderror"
                                       placeholder="contoh: situs-terlarang.com"
                                       value="{{ old('domain') }}"
                                       required>
                                @error('domain')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="form-group">
                                <label for="notes">Catatan</label>
                                <input type="text" name="notes" id="notes"
                                       class="form-control @error('notes') is-invalid @enderror"
                                       placeholder="Alasan blacklist (opsional)"
                                       value="{{ old('notes') }}">
                                @error('notes')
                                    <span class="text-danger small">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-dark btn-block">
                                    <i class="fas fa-save mr-1"></i> Simpan
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Blacklist Table -->
<div class="row">
    <div class="col-12">
        <div class="card card-dark card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-ban mr-1"></i> Daftar Blacklist
                </h3>
                <div class="card-tools">
                    <span class="badge badge-dark ml-2">{{ $domains->total() ?? 0 }} domain</span>
                </div>
            </div>
            <div class="card-body table-responsive p-0">
                <table class="table table-hover text-nowrap">
                    <thead class="thead-dark">
                        <tr>
                            <th style="width: 40px">#</th>
                            <th>Domain</th>
                            <th>Catatan</th>
                            <th>Dibuat</th>
                            <th style="width: 120px">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($domains ?? [] as $index => $item)
                        <tr>
                            <td>{{ $domains->firstItem() + $index ?? $loop->iteration }}</td>
                            <td><code>{{ $item->domain }}</code></td>
                            <td>{{ $item->notes ?: '-' }}</td>
                            <td>{{ \Carbon\Carbon::parse($item->created_at)->format('d M Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-warning"
                                        data-toggle="modal"
                                        data-target="#editModal{{ $item->id }}"
                                        title="Edit">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-danger"
                                        onclick="confirmDelete({{ $item->id }}, '{{ addslashes($item->domain) }}')"
                                        title="Hapus">
                                    <i class="fas fa-trash"></i>
                                </button>

                                <!-- Edit Modal -->
                                <div class="modal fade" id="editModal{{ $item->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header bg-dark">
                                                <h5 class="modal-title">
                                                    <i class="fas fa-edit mr-1"></i> Edit Domain
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <form method="POST" action="{{ route('blacklist.update', $item->id) }}">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="form-group">
                                                        <label for="domain{{ $item->id }}">Domain</label>
                                                        <input type="text" name="domain"
                                                               id="domain{{ $item->id }}"
                                                               class="form-control"
                                                               value="{{ $item->domain }}"
                                                               required>
                                                    </div>
                                                    <div class="form-group">
                                                        <label for="notes{{ $item->id }}">Catatan</label>
                                                        <input type="text" name="notes"
                                                               id="notes{{ $item->id }}"
                                                               class="form-control"
                                                               value="{{ $item->notes }}">
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                    <button type="submit" class="btn btn-dark">
                                                        <i class="fas fa-save mr-1"></i> Perbarui
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x d-block mb-2"></i>
                                Belum ada domain dalam blacklist.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if(isset($domains) && $domains->hasPages())
            <div class="card-footer clearfix">
                <div class="float-right">
                    {{ $domains->links() }}
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<!-- Delete Confirmation Form (hidden) -->
<form id="deleteForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
function confirmDelete(id, domain) {
    if (confirm('Apakah Anda yakin ingin menghapus domain "' + domain + '" dari blacklist? Tindakan ini tidak dapat dibatalkan.')) {
        var form = document.getElementById('deleteForm');
        form.action = '{{ url('blacklist') }}/' + id;
        form.submit();
    }
}
</script>
@endsection
