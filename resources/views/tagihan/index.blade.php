@extends('layouts.app')
@section('title', 'Daftar Tagihan')
@section('content')
<div class="row">
    <div class="col-lg-12">
        @include('layouts.alerts')
        @if(isset($errorMessage))
            <div class="alert alert-warning">
                {{ $errorMessage }}
            </div>
        @endif

        {{-- CARD UTAMA --}}
        <div class="card">
        <div class="card-header d-flex justify-content-between align-items-start flex-wrap">
            {{-- Judul di kiri --}}
            <div>
                <h4 class="card-title mb-0">Daftar Tagihan Siswa</h4>

                {{-- FILTER JENJANG & KELAS --}}
                <form action="{{ route('tagihan.index') }}" method="GET" class="row g-2 mt-4">
                    {{-- Filter Jenjang --}}
                    <div class="col-md-5 col-12">
                        <select name="jenjang" class="form-select" onchange="this.form.submit()">
                            <option value="" disabled {{ $jenjangFilter === null ? 'selected' : '' }}>Pilih Jenjang</option>
                            <option value="0" {{ $jenjangFilter === null ? 'selected' : '' }}>Semua Jenjang</option>
                            
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}" {{ $jenjangFilter == $j->id ? 'selected' : '' }}>
                                    {{ $j->nama_jenjang }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter Kelas --}}
                    <div class="col-md-4 col-12">
                        <select name="kelas" class="form-select" onchange="this.form.submit()">
                            <option value="" disabled {{ $kelasFilter === null ? 'selected' : '' }}>Pilih Kelas</option>
                            <option value="0" {{ $kelasFilter === null ? 'selected' : '' }}>Semua Kelas</option>
                            @foreach($kelas as $k)
                                @if (!$jenjangFilter || $jenjangFilter == $k->id_jenjang || $jenjangFilter == 0)
                                    <option value="{{ $k->id }}" {{ $kelasFilter == $k->id ? 'selected' : '' }}>
                                        {{ $k->jenjang->nama_jenjang ?? 'Jenjang' }} - {{ $k->tingkat }} ({{ $k->nama_kelas }})
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Reset Button --}}
                    <div class="col-md-auto col-12 d-flex align-items-center">
                        <a href="{{ route('tagihan.index') }}" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Reset Filter">Reset</a>
                    </div>
                </form>
            </div>

            {{-- Tombol di kanan atas --}}
            <div class="d-flex gap-2 mt-2 mt-lg-0">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambahTagihanTunggalModal">
                     Tambah Tagihan Tunggal
                </button>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#tambahTagihanMassalModal">
                     Tambah Tagihan Massal
                </button>
            </div>
        </div>


            {{-- TABEL SISWA --}}
            <div class="card-body p-3">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Siswa</th>
                                <th>Jenjang/Kelas</th>
                                <th>Status Aktif</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($siswas as $siswa)
                            <tr class="align-middle">
                                <td>{{ $siswas->firstItem() + $loop->index }}</td>
                                <td>{{ $siswa->nama_siswa }}</td>
                                <td>
                                    @if ($siswa->kelasAktif && $siswa->kelasAktif->kelas)
                                        {{ $siswa->kelasAktif->kelas->jenjang->nama_jenjang ?? 'Jenjang' }} 
                                        - {{ $siswa->kelasAktif->kelas->tingkat ?? 'N/A' }} 
                                        ({{ $siswa->kelasAktif->kelas->nama_kelas ?? 'N/A' }})
                                    @else
                                        N/A
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $siswa->status_aktif == 'Aktif' ? 'success' : 'danger' }}">
                                        {{ $siswa->status_aktif }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('tagihan.show', $siswa->id) }}" class="btn btn-info btn-sm">
                                         Lihat Tagihan
                                    </a>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">Tidak ada siswa aktif.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- PAGINATION CUSTOM --}}
            <div class="card-footer d-flex justify-content-between align-items-center">

                {{-- Tombol Previous --}}
                @if ($siswas->onFirstPage())
                    <span class="btn btn-secondary disabled">Previous</span>
                @else
                    <a href="{{ $siswas->previousPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                    class="btn btn-primary">Previous</a>
                @endif

                {{-- Info Halaman --}}
                <span class="fw-bold">
                    Halaman {{ $siswas->currentPage() }} dari {{ $siswas->lastPage() }}
                </span>

                {{-- Tombol Next --}}
                @if ($siswas->hasMorePages())
                    <a href="{{ $siswas->nextPageUrl() }}&{{ http_build_query(request()->except('page')) }}"
                    class="btn btn-primary">Next</a>
                @else
                    <span class="btn btn-secondary disabled">Next</span>
                @endif

            </div>

        </div>
    </div>
</div>
@endsection

{{-- ====================== --}}
{{-- MODAL TAMBAH TAGIHAN --}}
{{-- ====================== --}}
<div class="modal fade" id="tambahTagihanTunggalModal" tabindex="-1" aria-labelledby="tambahTagihanTunggalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="tambahTagihanTunggalModalLabel">Tambah Tagihan Tunggal</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('tagihan.store') }}" method="POST">
                @csrf
                <div class="modal-body">

                    {{-- FILTER JENJANG --}}
                    <div class="mb-3">
                        <label>Jenjang</label>
                        <select name="jenjang_id_filter" id="jenjangTunggalFilter" class="form-select" required>
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- FILTER KELAS BARU DITAMBAH --}}
                    <div class="mb-3">
                        <label>Kelas</label>
                        <select name="kelas_id_filter" id="kelasTunggalFilter" class="form-select" disabled required>
                            <option value="">-- Pilih Kelas --</option>
                            {{-- Opsi akan diisi oleh JavaScript --}}
                        </select>
                    </div>

                    {{-- SISWA (Dropdown ini yang difilter oleh Jenjang & Kelas) --}}
                    <div class="mb-3">
                        <label>Siswa</label>
                        {{-- ID Siswa yang akan dikirim ke Controller --}}
                        <select name="id_siswa" id="idSiswaTunggal" class="form-select" disabled required> 
                            <option value="">-- Pilih Siswa --</option>
                            {{-- Semua siswa akan di-loop di sini dan disaring oleh JS --}}
                            @foreach($siswaModal as $s)
                                <option 
                                    value="{{ $s->id }}" 
                                    data-jenjang="{{ $s->kelas->id_jenjang ?? '0' }}" {{-- Tambahkan data Jenjang --}}
                                    data-kelas="{{ $s->id_kelas ?? '0' }}" {{-- Tambahkan data Kelas --}}
                                    style="display: none;" {{-- Sembunyikan semua secara default --}}
                                >
                                    {{ $s->nama_siswa }} 
                                    ({{ $s->kelas->jenjang->nama_jenjang ?? 'N/A' }} - {{ $s->kelas->tingkat ?? 'N/A' }} ({{ $s->kelas->nama_kelas ?? 'N/A' }}))
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <input type="hidden" name="status" value="Belum Bayar">

                    {{-- JENIS PEMBAYARAN --}}
                    <div class="mb-3">
                        <label>Kategori Pembayaran</label>
                        <select name="id_jenis_pembayaran" id="jenisPembayaranTunggal" class="form-select" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($jenisPembayaran as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- [Lain-lain... Tahun Ajaran, Bulan/Tahun, Nominal, Diskon, Jatuh Tempo tetap sama] --}}
                    
                    <div class="mb-3">
                        <label>Tahun Ajaran</label>
                        <select name="id_tahun_ajaran" id="tahunAjaranTunggal" class="form-select" required>
                            @foreach($tahunAjaran as $ta)
                                <option value="{{ $ta->id }}" {{ $ta->is_aktif ? 'selected' : '' }}>
                                    {{ $ta->nama_tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>Bulan Tagihan</label>
                        <select name="bulan_tagihan" id="bulanTagihan" class="form-select">
                            <option value="">Pilih Bulan</option>
                            @for($i=1;$i<=12;$i++)
                                <option value="{{ $i }}">{{ \Carbon\Carbon::create(null, $i)->format('F') }}</option>
                            @endfor
                        </select>
                    </div>
                        {{-- <div class="col-md-6 mb-3">
                            <label>Tahun Tagihan</label>
                            <input type="number" name="tahun_tagihan" class="form-control" value="{{ date('Y') }}" required>
                        </div> --}}

                    <div class="mb-3">
                        <label>Total Tagihan (Rp.)</label>
                        <input type="number" name="total_tagihan" id="totalTagihanTunggal" class="form-control" required>
                    </div>
                    
                    <div class="mb-3">
                        <label>Diskon (Rp.)</label>
                        <input type="number" id="diskonTagihanTunggal" class="form-control" readonly>
                    </div>

                    <div class="mb-3">
                        <label>Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="{{ now()->addDays(7)->format('Y-m-d') }}" required>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" type="submit">Simpan Tagihan Tunggal</button>
                </div>
            </form>

        </div>
    </div>
</div>

<div class="modal fade" id="tambahTagihanMassalModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Tagihan Massal Per Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form action="{{ route('tagihan.storeMassal') }}" method="POST">
                @csrf
                <div class="modal-body">

                    <div class="mb-3">
                        <label>Kategori Pembayaran</label>
                        <select name="id_jenis_pembayaran" id="jenisMassal" class="form-control" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($jenisPembayaran as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenis }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- JENJANG --}}
                    <div class="mb-3">
                        <label>Jenjang</label>
                        <select name="jenjang_id" id="jenjangMassal" class="form-control" required>
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- KELAS (TAMBAH DISABLED) --}}
                    <div class="mb-3">
                        <label>Kelas</label>
                        {{-- **PERUBAHAN DISINI:** Tambahkan 'disabled' --}}
                        <select name="id_kelas" id="kelasMassal" class="form-control" disabled required>
                            <option value="">-- Pilih Kelas --</option>
                            {{-- Opsi kelas akan diisi melalui JavaScript --}}
                        </select>
                    </div>

                    {{-- TAHUN AJARAN --}}
                    <div class="mb-3">
                        <label>Tahun Ajaran</label>
                        <select name="id_tahun_ajaran" id="tahunMassal" class="form-control" required>
                            @foreach($tahunAjaran as $ta)
                                <option value="{{ $ta->id }}" {{ $ta->is_aktif ? 'selected' : '' }}>
                                    {{ $ta->nama_tahun }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    {{-- BULAN + TAHUN --}}
                    <div class="row">
                        <div class="col-6">
                            <label>Bulan Tagihan</label>
                            <select class="form-control" name="bulan_tagihan">
                                <option value="">Pilih Bulan</option>
                                @for($i=1;$i<=12;$i++)
                                    <option value="{{ $i }}">{{ \Carbon\Carbon::create(null, $i)->format('F') }}</option>
                                @endfor
                            </select>
                        </div>
                        {{-- <div class="col-6">
                            <label>Tahun Tagihan</label>
                            <input type="number" name="tahun_tagihan" class="form-control" value="{{ date('Y') }}" required>
                        </div> --}}
                    </div>

                    {{-- JATUH TEMPO --}}
                    <div class="mt-3">
                        <label>Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control" 
                                value="{{ now()->addDays(7)->format('Y-m-d') }}" required>
                    </div>

                    {{-- NOMINAL MASSAL --}}
                    <div class="mb-3 mt-3">
                        <label>Total Tagihan (Rp.)</label>
                        <input type="number" name="total_tagihan" id="totalTagihanMassal" class="form-control" required>
                    </div>

                    {{-- DISKON MASSAL --}}
                    <div class="mb-3">
                        <label>Diskon (Rp.)</label>
                        <input type="number" id="diskonMassal" class="form-control" readonly>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan Tagihan Massal</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- =================================== --}}
{{-- SCRIPT PENGAMBILAN NOMINAL & FILTER --}}
{{-- =================================== --}}
<script>
    // =====================================
    // VARIABEL GLOBAL UNTUK MODAL TUNGGAL
    // =====================================
    // ID baru yang telah dimodifikasi di Modal Tagihan Tunggal
    const jenjangTunggalFilter = document.getElementById('jenjangTunggalFilter');
    const kelasTunggalFilter = document.getElementById('kelasTunggalFilter');
    const idSiswaTunggal = document.getElementById('idSiswaTunggal');
    const jenisPembayaranTunggal = document.getElementById('jenisPembayaranTunggal');
    const tahunAjaranTunggal = document.getElementById('tahunAjaranTunggal');
    const totalTagihanTunggal = document.getElementById('totalTagihanTunggal');
    const diskonTagihanTunggal = document.getElementById('diskonTagihanTunggal');
    
    // Data Siswa dan Kelas yang diperlukan untuk filter sisi klien
    const allKelasForFilter = @json($kelas); 
    const allSiswaOptions = idSiswaTunggal ? idSiswaTunggal.querySelectorAll('option:not([value=""])') : [];


    // ==================================================
    // FUNGSI UTAMA 1: LOAD NOMINAL TUNGGAL (Mengambil Nominal)
    // ==================================================
    function loadNominalTunggal() {
        let idJenis = jenisPembayaranTunggal ? jenisPembayaranTunggal.value : null;
        let idSiswa = idSiswaTunggal ? idSiswaTunggal.value : null;
        let idTahun = tahunAjaranTunggal ? tahunAjaranTunggal.value : null;

        if (!idJenis || !idSiswa || !idTahun) {
            if (totalTagihanTunggal) totalTagihanTunggal.value = '';
            if (diskonTagihanTunggal) diskonTagihanTunggal.value = '';
            return;
        }

        fetch(`/tagihan/get-nominal/${idSiswa}/${idJenis}/${idTahun}`)
            .then(res => res.json())
            .then(data => {
                if (data.nominal !== null) {
                    if (totalTagihanTunggal) totalTagihanTunggal.value = data.nominal;
                    if (diskonTagihanTunggal) diskonTagihanTunggal.value = data.diskon;
                } else {
                    if (totalTagihanTunggal) totalTagihanTunggal.value = '';
                    if (diskonTagihanTunggal) diskonTagihanTunggal.value = '';
                    alert("Nominal tidak ditemukan di Atur Nominal.");
                }
            })
            .catch(err => console.error(err));
    }


    // =========================================================
    // FUNGSI UTAMA 2: FILTER KELAS & SISWA UNTUK MODAL TUNGGAL
    // =========================================================

    // --- FUNGSI 2A: FILTER KELAS BERDASARKAN JENJANG (Modal Tunggal) ---
    function filterKelasTunggal() {
        if (!jenjangTunggalFilter || !kelasTunggalFilter) return;

        const selectedJenjangId = jenjangTunggalFilter.value;
        
        // Reset Kelas & Siswa
        kelasTunggalFilter.innerHTML = '<option value="">-- Pilih Kelas --</option>';
        kelasTunggalFilter.disabled = true;
        if (idSiswaTunggal) {
             idSiswaTunggal.disabled = true;
             idSiswaTunggal.value = "";
        }
        
        // Reset Nominal
        if (totalTagihanTunggal) totalTagihanTunggal.value = '';
        if (diskonTagihanTunggal) diskonTagihanTunggal.value = '';

        if (!selectedJenjangId) return;

        // Filter data kelas di sisi klien (JavaScript)
        const filteredKelas = allKelasForFilter.filter(kelas => {
            return kelas.id_jenjang == selectedJenjangId; 
        });
        
        if (filteredKelas.length > 0) {
            filteredKelas.forEach(kelas => {
                let option = document.createElement('option');
                option.value = kelas.id;
                // Format: Jenjang - Tingkat X (Nama Kelas)
                option.textContent = `${kelas.jenjang.nama_jenjang} - ${kelas.tingkat} (${kelas.nama_kelas})`;
                kelasTunggalFilter.appendChild(option);
            });
            kelasTunggalFilter.disabled = false;
        }
    }


    // --- FUNGSI 2B: FILTER SISWA BERDASARKAN KELAS (Modal Tunggal) ---
    function filterSiswaTunggal() {
        if (!kelasTunggalFilter || !idSiswaTunggal) return;

        const selectedKelasId = kelasTunggalFilter.value;
        
        // Reset Siswa
        idSiswaTunggal.value = "";
        idSiswaTunggal.disabled = true;

        // Reset Nominal
        if (totalTagihanTunggal) totalTagihanTunggal.value = '';
        if (diskonTagihanTunggal) diskonTagihanTunggal.value = '';

        // Sembunyikan semua opsi siswa dan hapus selected
        allSiswaOptions.forEach(option => {
            option.style.display = 'none';
            option.removeAttribute('selected'); 
        });

        if (!selectedKelasId) return;

        // Tampilkan opsi siswa yang cocok
        allSiswaOptions.forEach(option => {
            if (option.getAttribute('data-kelas') === selectedKelasId) {
                option.style.display = 'block';
            }
        });

        idSiswaTunggal.disabled = false;
    }


    // =====================================
    // EVENT LISTENERS MODAL TUNGGAL
    // =====================================
    if (jenjangTunggalFilter) jenjangTunggalFilter.addEventListener('change', filterKelasTunggal);
    if (kelasTunggalFilter) kelasTunggalFilter.addEventListener('change', filterSiswaTunggal);
    if (idSiswaTunggal) idSiswaTunggal.addEventListener('change', loadNominalTunggal);
    if (jenisPembayaranTunggal) jenisPembayaranTunggal.addEventListener('change', loadNominalTunggal);
    if (tahunAjaranTunggal) tahunAjaranTunggal.addEventListener('change', loadNominalTunggal);


    // =======================================
    // FUNGSI DAN LISTENERS MODAL MASSAL (TETAP)
    // =======================================
    function loadNominalMassal() {
        let idJenis = document.getElementById('jenisMassal').value;
        let idKelas = document.getElementById('kelasMassal').value;
        let idTahun = document.getElementById('tahunMassal').value;

        if (!idJenis || !idKelas || !idTahun) return;

        fetch(`/tagihan/get-nominal-massal/${idKelas}/${idJenis}/${idTahun}`)
            .then(res => res.json())
            .then(data => {
                if (data.nominal !== null) {
                    document.getElementById('totalTagihanMassal').value = data.nominal;
                    document.getElementById('diskonMassal').value = data.diskon;
                } else {
                    document.getElementById('totalTagihanMassal').value = '';
                    document.getElementById('diskonMassal').value = '';
                    alert("Nominal tidak ditemukan di Atur Nominal.");
                }
            })
            .catch(err => console.error(err));
    }

    document.getElementById('jenisMassal').addEventListener('change', loadNominalMassal);
    document.getElementById('kelasMassal').addEventListener('change', loadNominalMassal);
    document.getElementById('tahunMassal').addEventListener('change', loadNominalMassal);

    // =======================================
    // SCRIPT FILTER KELAS MASSAL
    // =======================================
    const allKelas = @json($kelas); 
    const jenjangDropdown = document.getElementById('jenjangMassal');
    const kelasDropdown = document.getElementById('kelasMassal');
    const totalTagihanMassal = document.getElementById('totalTagihanMassal');
    const diskonMassal = document.getElementById('diskonMassal');

    function filterKelasMassal() { 
        const selectedJenjangId = jenjangDropdown.value;
        
        // Reset dropdown Kelas dan nominal
        kelasDropdown.innerHTML = '<option value="">-- Pilih Kelas --</option>';
        kelasDropdown.disabled = true;
        kelasDropdown.value = '';
        totalTagihanMassal.value = '';
        diskonMassal.value = '';

        if (!selectedJenjangId) return;

        // Filter data kelas
        const filteredKelas = allKelas.filter(kelas => kelas.id_jenjang == selectedJenjangId);
        
        if (filteredKelas.length > 0) {
            filteredKelas.forEach(kelas => {
                let option = document.createElement('option');
                option.value = kelas.id;
                option.textContent = `${kelas.jenjang.nama_jenjang} - ${kelas.tingkat} (${kelas.nama_kelas})`;
                kelasDropdown.appendChild(option);
            });
            kelasDropdown.disabled = false;

            // **Jika hanya ada 1 kelas, auto pilih dan load nominal**
            if(filteredKelas.length === 1) {
                kelasDropdown.value = filteredKelas[0].id;
                loadNominalMassal();
            }
        } else {
            alert('Tidak ada data kelas untuk jenjang ini.');
        }
    }

    // Trigger saat Jenjang diganti
    jenjangDropdown.addEventListener('change', filterKelasMassal);

    // Trigger saat Kelas diganti
    kelasDropdown.addEventListener('change', loadNominalMassal);

</script>