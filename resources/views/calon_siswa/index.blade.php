@extends('layouts.app') 

@section('content')
<div class="container-fluid p-4">
    <h2 class="text-xl font-semibold mb-4">Manajemen Pendaftar Baru (Menunggu Persetujuan)</h2>
    <p class="text-sm text-gray-600 mb-4">Daftar calon siswa yang sudah **Lunas** biaya pendaftaran dan siap diaktifkan.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Calon Siswa</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenjang</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Pembayaran</th>
                            <th class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($applicants as $index => $applicant)
                        <tr>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $applicants->firstItem() + $index }}</td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                <strong>{{ $applicant->nama_siswa }}</strong><br>
                                <small class="text-muted">{{ $applicant->email_wali }}</small>
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-gray-500">{{ $applicant->jenjang->name ?? 'N/A' }}</td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm text-green-600 font-semibold">
                                <span class="badge bg-success">{{ $applicant->payment_status }}</span> (Rp {{ number_format($applicant->amount, 0, ',', '.') }})
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap text-sm font-medium">
                                <!-- Tombol Aktifkan Siswa -->
                                <button type="button" class="btn btn-success btn-sm" 
                                        data-bs-toggle="modal" data-bs-target="#approveModal{{ $applicant->id }}">
                                    Aktifkan Siswa
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-3 py-3 whitespace-nowrap text-sm text-center text-gray-500">
                                Tidak ada pendaftar baru yang menunggu persetujuan (Lunas dan Diajukan).
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <div class="mt-4">
        {{ $applicants->links() }}
    </div>
</div>

<!-- Modal Approval -->
@foreach($applicants as $applicant)
<div class="modal fade" id="approveModal{{ $applicant->id }}" tabindex="-1" aria-labelledby="approveModalLabel{{ $applicant->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('admin.calon_siswa.approve', $applicant->id) }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="approveModalLabel{{ $applicant->id }}">Aktivasi Siswa: {{ $applicant->nama_siswa }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3 text-success">
                        **Pendaftar Lunas.** Data siap dipindahkan ke Siswa Aktif.
                    </p>
                    
                    <div class="mb-3">
                        <label for="nisn" class="form-label font-weight-bold">NIS/NISN Siswa:</label>
                        <input type="text" class="form-control" id="nisn" name="nisn" required placeholder="Contoh: 2024001">
                        <small class="text-muted">NIS ini harus unik dan wajib diisi admin.</small>
                    </div>

                    <div class="mb-3">
                        <label for="id_kelas" class="form-label font-weight-bold">Pilih Kelas Masuk:</label>
                        <select class="form-select" id="id_kelas" name="id_kelas" required>
                            <option value="">Pilih Kelas...</option>
                            @foreach($kelas as $k)
                                <option value="{{ $k->id }}">{{ $k->nama_kelas ?? 'N/A' }}</option> 
                            @endforeach
                        </select>
                        <small class="text-muted">Siswa akan ditempatkan di kelas ini.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Konfirmasi & Aktifkan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@endsection