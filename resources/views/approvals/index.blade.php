@extends('dashboard.index')

@section('title', 'Menunggu Approval Saya')
@section('route-breadcrumb', 'approvals.index')
@section('breadcrumb', 'Approval')

@section('content')

@if($errors->any())
    <x-ui.alert type="danger" class="mb-3">{{ $errors->first() }}</x-ui.alert>
@endif

<x-ui.card>
    @if($requests->isEmpty())
        <x-ui.empty-state
            icon='<iconify-icon icon="mdi:check-decagram-outline" style="font-size:26px;opacity:.5"></iconify-icon>'
            title="Tidak ada yang menunggu"
            description="Semua permintaan sudah diproses. Belum ada yang butuh persetujuan Anda saat ini." />
    @else
        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Jenis</th>
                        <th>Diajukan Oleh</th>
                        <th>Detail</th>
                        <th>Tanggal</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requests as $req)
                        <tr>
                            <td><x-ui.badge variant="info">{{ $req->flow->name }}</x-ui.badge></td>
                            <td>{{ $req->requester->name ?? '-' }}</td>
                            <td>
                                @if($req->approvable && method_exists($req->approvable, 'approvalSummary'))
                                    {{ $req->approvable->approvalSummary() }}
                                @else
                                    {{ class_basename($req->approvable_type) }} #{{ $req->approvable_id }}
                                @endif
                            </td>
                            <td class="small text-muted">{{ $req->created_at->translatedFormat('d M Y, H:i') }}</td>
                            <td class="text-end">
                                <div class="d-flex gap-2 justify-content-end">
                                    <form method="POST" action="{{ route('approvals.approve', $req) }}">
                                        @csrf
                                        <x-ui.button type="submit" variant="success" size="sm">Setujui</x-ui.button>
                                    </form>
                                    <button type="button" class="ui-btn ui-btn-sm ui-btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $req->id }}">
                                        Tolak
                                    </button>
                                </div>

                                <div class="modal fade" id="rejectModal{{ $req->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('approvals.reject', $req) }}">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Permintaan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body text-start">
                                                    <x-ui.form-field label="Alasan Penolakan" name="note" :required="true">
                                                        <textarea name="note" class="form-control" rows="3" required></textarea>
                                                    </x-ui.form-field>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                                                    <x-ui.button type="submit" variant="danger">Tolak Permintaan</x-ui.button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $requests->links() }}</div>
    @endif
</x-ui.card>

@endsection
