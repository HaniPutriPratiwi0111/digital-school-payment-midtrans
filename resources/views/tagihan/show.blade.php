@extends('layouts.app')
@section('title', 'Detail Tagihan')

@section('content')
<div class="container">

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">

            <div>
                <h4 class="card-title mb-0">
                    Detail Tagihan Siswa: {{ $siswa->nama_siswa }}
                </h4>

                {{-- Dropdown filter Tahun Ajaran (Diubah agar mirip gambar) --}}
                <form action="{{ route('tagihan.show', $siswa->id) }}" method="GET" class="mt-2">
                    <div class="mb-3" style="width: 200px;">
                        {{-- Label di atas elemen select --}}
                        <label for="tahun_ajaran_filter" class="form-label mb-0">Tahun Ajaran</label>

                        {{-- Elemen Select yang di-highlight --}}
                        <select name="tahun_ajaran_id" id="tahun_ajaran_filter" class="form-select" onchange="this.form.submit()">
                            {{-- Opsi Default sesuai gambar (jika ada) --}}
                            <option value="">-- Pilih Tahun Ajaran --</option>

                            @foreach($tahunAjaran as $ta)
                                <option value="{{ $ta->id }}"
                                    {{ $ta->id == $selectedTahunId ? 'selected' : '' }}>
                                    {{ $ta->nama_tahun }} {{ $ta->is_aktif ? '(Aktif)' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </form>
                {{-- Akhir Dropdown filter Tahun Ajaran --}}

            </div>

            <a href="{{ route('tagihan.index', request()->only(['jenjang','kelas'])) }}" class="btn btn-secondary btn-sm">
                Kembali ke Daftar Siswa
            </a>

        </div>

        <div class="card-body p-3">
            <div class="table-responsive">

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Jenis Pembayaran</th>
                            <th>Periode</th>
                            <th>Tagihan</th>
                            <th>Jatuh Tempo</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($tagihans as $tagihan)
                        <tr class="align-middle">

                            <td class="text-center">{{ $loop->iteration }}</td>

                            <td>{{ $tagihan->jenisPembayaran->nama_jenis ?? 'N/A' }}</td>

                            <td>
                                @if($tagihan->bulan_tagihan)
                                    {{ \Carbon\Carbon::create(null, $tagihan->bulan_tagihan)->format('F') }}
                                    {{ $tagihan->tahun_tagihan }}
                                @else
                                    {{ $tagihan->tahun_tagihan ?? 'Non-Bulanan' }}
                                @endif
                            </td>

                            <td class="text-end">Rp {{ number_format($tagihan->total_tagihan, 0, ',', '.') }}</td>

                            <td>
                                {{ $tagihan->tanggal_jatuh_tempo
                                    ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('d/m/Y')
                                    : '-' }}
                            </td>

                            <td class="text-center">
                                @php
                                    $warna = [
                                        'Lunas' => 'success',
                                        'Lunas Partial' => 'warning',
                                        'Belum Bayar' => 'danger',
                                        'Batal' => 'secondary'
                                    ][$tagihan->status] ?? 'danger';
                                @endphp
                                <span class="badge bg-{{ $warna }}">{{ $tagihan->status }}</span>
                            </td>

                            <td class="text-center">
                                <button class="btn btn-warning btn-sm"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editTagihanModal{{ $tagihan->id }}">
                                     Edit
                                </button>
                            </td>

                        </tr>

                        {{-- Modal Edit Tagihan --}}
                        <div class="modal fade" id="editTagihanModal{{ $tagihan->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">

                                    <div class="modal-header">
                                        <h5 class="modal-title">Edit Tagihan {{ $siswa->nama_siswa }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <form action="{{ route('tagihan.update', $tagihan->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-body">

                                            {{-- Jenis Pembayaran --}}
                                            <div class="mb-2">
                                                <label>Jenis Pembayaran</label>
                                                <select name="id_jenis_pembayaran"
                                                            id="jenisPembayaranEdit{{ $tagihan->id }}"
                                                            class="form-control" required>
                                                     @foreach($jenisPembayaran as $jp)
                                                         <option value="{{ $jp->id }}"
                                                             {{ $jp->id == $tagihan->id_jenis_pembayaran ? 'selected' : '' }}>
                                                             {{ $jp->nama_jenis }}
                                                         </option>
                                                     @endforeach
                                                </select>
                                            </div>

                                            {{-- Tahun Ajaran --}}
                                            <div class="mb-2">
                                                <label>Tahun Ajaran</label>
                                                <select name="id_tahun_ajaran"
                                                            id="tahunAjaranEdit{{ $tagihan->id }}"
                                                            class="form-control" required>
                                                     @foreach($tahunAjaran as $ta)
                                                         <option value="{{ $ta->id }}"
                                                             {{ $ta->id == $tagihan->id_tahun_ajaran ? 'selected' : '' }}>
                                                             {{ $ta->nama_tahun }}
                                                         </option>
                                                     @endforeach
                                                </select>
                                            </div>

                                            {{-- Bulan --}}
                                            <div class="mb-2">
                                                <label>Bulan</label>
                                                <select name="bulan_tagihan" class="form-control">
                                                     <option value="">-</option>
                                                     @for($i=1;$i<=12;$i++)
                                                         <option value="{{ $i }}"
                                                             {{ $tagihan->bulan_tagihan == $i ? 'selected' : '' }}>
                                                             {{ \Carbon\Carbon::create(null, $i)->format('F') }}
                                                         </option>
                                                     @endfor
                                                </select>
                                            </div>

                                            {{-- Total Tagihan --}}
                                            <div class="mb-2">
                                                <label>Total Tagihan (Rp.)</label>
                                                <input type="number" class="form-control" id="totalTagihanEdit{{ $tagihan->id }}" name="total_tagihan" value="{{ $tagihan->total_tagihan }}" required readonly>
                                            </div>

                                            {{-- Diskon --}}
                                            <div class="mb-2">
                                                <label>Diskon (Rp.)</label>
                                                <input type="number" class="form-control"
                                                     id="diskonTagihanEdit{{ $tagihan->id }}"
                                                     name="nominal_diskon"
                                                     value="{{ $tagihan->nominal_diskon }}" readonly>
                                            </div>

                                            {{-- Jatuh Tempo --}}
                                            <div class="mb-2">
                                                <label>Jatuh Tempo</label>
                                                <input type="date" name="tanggal_jatuh_tempo" class="form-control"
                                                     value="{{ $tagihan->tanggal_jatuh_tempo ? \Carbon\Carbon::parse($tagihan->tanggal_jatuh_tempo)->format('Y-m-d') : '' }}">
                                            </div>

                                            {{-- Status --}}
                                            <div class="mb-2">
                                                <label>Status Pembayaran</label>
                                                <select name="status" class="form-control">
                                                     <option value="Belum Bayar" {{ $tagihan->status == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                                     <option value="Menunggu Konfirmasi" {{ $tagihan->status == 'Menunggu Konfirmasi' ? 'selected' : '' }}>Menunggu Konfirmasi</option>
                                                     <option value="Lunas" {{ $tagihan->status == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button class="btn btn-primary">Update Perubahan</button>
                                        </div>
                                    </form>

                                </div>
                            </div>
                        </div>

                        @empty
                        <tr>
                            <td colspan="7" class="text-center p-4">Tidak ada tagihan untuk siswa ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

            </div>
        </div>
    </div>

</div>
@endsection

{{-- Script Auto Hitung Nominal & Diskon --}}
<script>
@foreach($tagihans as $tagihan)
document.addEventListener("DOMContentLoaded", function () {
    function loadNominal{{ $tagihan->id }}() {
        let idJenis = document.getElementById("jenisPembayaranEdit{{ $tagihan->id }}").value;
        let idTahun = document.getElementById("tahunAjaranEdit{{ $tagihan->id }}").value;
        let idSiswa = "{{ $siswa->id }}";

        fetch(`/tagihan/get-nominal/${idSiswa}/${idJenis}/${idTahun}`)
            .then(res => res.json())
            .then(data => {
                if (data.nominal !== null) {
                    document.getElementById("totalTagihanEdit{{ $tagihan->id }}").value = data.nominal;
                    document.getElementById("diskonTagihanEdit{{ $tagihan->id }}").value = data.diskon;
                }
            });
    }

    document.getElementById("jenisPembayaranEdit{{ $tagihan->id }}")
        .addEventListener("change", loadNominal{{ $tagihan->id }});
    document.getElementById("tahunAjaranEdit{{ $tagihan->id }}")
        .addEventListener("change", loadNominal{{ $tagihan->id }});
});
@endforeach
</script>