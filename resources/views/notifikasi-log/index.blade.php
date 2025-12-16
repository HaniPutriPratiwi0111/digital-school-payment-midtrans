@extends('layouts.app')
@section('title', 'Log Notifikasi')
@section('content')
<div class="row">
    <div class="col-lg-12">
        <div class="card">
            <div class="card-header"><h4 class="card-title">Log Pengiriman Notifikasi (WA/Email)</h4></div>
            <div class="card-body p-3">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Waktu Kirim</th>
                            <th>Siswa</th>
                            <th>Tipe</th>
                            <th>Isi Pesan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($logs as $log)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ \Carbon\Carbon::parse($log->waktu_kirim)->format('d/m H:i') }}</td>
                            <td>{{ $log->siswa->nama_siswa }}</td>
                            <td>{{ $log->tipe_notifikasi }}</td>
                            <td>{{ Str::limit($log->isi_pesan, 50) }}</td>
                            <td><span class="badge bg-{{ $log->status_kirim == 'Sukses' ? 'success' : ($log->status_kirim == 'Pending' ? 'secondary' : 'danger') }}">{{ $log->status_kirim }}</span></td>
                            <td>
                                <a href="{{ route('notifikasi-log.show', $log) }}" class="btn btn-sm btn-info">Detail</a>
                                <form action="{{ route('notifikasi-log.destroy', $log) }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer">{{ $logs->links() }}</div>
        </div>
    </div>
</div>
@endsection