@extends('layouts.app')

@section('title', 'Naik Kelas Massal')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h4 class="mb-0">Naik Kelas Massal</h4>
            </div>
            <div class="card-body">

                {{-- Alert error / success --}}
                @if(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $err)
                                <li>{{ $err }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="alert alert-warning">
                    <strong>Perhatian!</strong><br>
                    Fitur ini digunakan <strong>di akhir tahun ajaran</strong> untuk memindahkan seluruh siswa dari kelas asal ke kelas berikutnya.
                    Pastikan pilihan kelas sudah benar karena perubahan ini bersifat permanen. Kamu bisa menandai siswa yang ingin tinggal kelas.
                </div>

                <form action="{{ route('siswa.naikKelas.proses') }}" method="POST">
                    @csrf

                    {{-- Tahun ajaran aktif --}}
                    <div class="mb-3">
                        <label class="form-label">Tahun Ajaran Aktif</label>
                        <input type="text" class="form-control" value="{{ $tahunAktif->nama_tahun }}" disabled>
                    </div>

                    {{-- Tahun sebelumnya (fixed) --}}
                    <div class="mb-3">
                        <label class="form-label">Tahun Sebelumnya</label>
                        <input type="text" class="form-control" value="{{ $tahunSebelumnya->nama_tahun ?? '-' }}" disabled>
                        <input type="hidden" name="id_tahun_sebelumnya" value="{{ $tahunSebelumnya->id ?? '' }}">
                    </div>

                    {{-- Pilih Jenjang --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Jenjang<span class="text-danger">*</span></label>
                        <select name="id_jenjang" id="jenjang" class="form-control" required>
                            <option value="">-- Pilih Jenjang --</option>
                            @foreach($jenjangs as $j)
                                <option value="{{ $j->id }}">{{ $j->nama_jenjang }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Kelas Asal --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Kelas Asal<span class="text-danger">*</span></label>
                        <select name="id_kelas_asal" id="kelas_asal" class="form-control" required>
                            <option value="">-- Memuat kelas asal... --</option>
                        </select>
                    </div>

                    {{-- Daftar siswa tinggal kelas --}}
                    <div class="mb-3" id="daftar_siswa_tinggal" style="display:none;">
                        <label class="form-label">Daftar Siswa (Tinggal Kelas)</label>
                        <div class="form-check mb-1">
                            <input type="checkbox" id="cek_semua_tinggal" class="form-check-input">
                            <label class="form-check-label">Cek semua siswa tinggal kelas</label>
                        </div>
                        <small id="peringatan_tinggal" class="text-danger fw-bold">Jangan dicentang jika tidak ada siswa yang tinggal kelas.</small>
                        <ul class="list-group mt-1"></ul>
                    </div>

                    {{-- Pilih Tingkat Tujuan --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Tingkat Tujuan<span class="text-danger">*</span></label>
                        <select id="tingkat_tujuan" class="form-control">
                            <option value="">-- Pilih Tingkat --</option>
                            @foreach($kelas->pluck('tingkat')->unique() as $tingkat)
                                <option value="{{ $tingkat }}">{{ $tingkat }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Pilih Nama Kelas Tujuan --}}
                    <div class="mb-3">
                        <label class="form-label">Pilih Nama Kelas Tujuan<span class="text-danger">*</span></label>
                        <select name="id_kelas_tujuan" id="kelas_tujuan" class="form-control">
                            <option value="">-- Pilih Kelas Sesuai Tingkat --</option>
                        </select>
                        <small id="peringatan_tujuan" class="text-muted fw-bold">
                            Jika ingin menambahkan siswa ke kelas lain, pilih nama kelas tujuan lagi untuk memasukkan siswa ke nama kelas lain.
                        </small>
                    </div>

                    {{-- Daftar siswa naik kelas --}}
                    <div class="mb-3" id="daftar_siswa_naik" style="display:none;">
                        <label class="form-label">Daftar Siswa (Naik Kelas)</label>
                        <ul class="list-group mt-1"></ul>
                    </div>
                    
                    {{-- **TEMPAT PENAMBAHAN KRUSIAL ADA DI SINI** --}}
                    <div id="hidden_naik_kelas_inputs" style="display: none;">
                        {{-- Ini adalah tempat JavaScript menyimpan semua checkbox siswa yang sudah dicentang (Kelas A, Kelas B, dst.) --}}
                    </div>

                    <hr>
                    <div class="text-end">
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Yakin ingin memproses naik kelas massal?')">
                            Proses Naik Kelas Massal
                        </button>
                        <a href="{{ route('siswa.index') }}" class="btn btn-secondary">Kembali</a>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    // --- Variabel DOM ---
    const kelasAsal = document.getElementById('kelas_asal');
    const daftarTinggal = document.querySelector('#daftar_siswa_tinggal ul');
    const cekSemuaTinggal = document.getElementById('cek_semua_tinggal');
    const daftarNaik = document.querySelector('#daftar_siswa_naik ul');
    const tingkatTujuan = document.getElementById('tingkat_tujuan');
    const kelasTujuan = document.getElementById('kelas_tujuan');
    const idTahunSebelumnya = "{{ $tahunSebelumnya->id ?? '' }}";
    
    // Pastikan ini ada di file Blade Anda, sebelum tombol submit
    const hiddenContainer = document.getElementById('hidden_naik_kelas_inputs'); 

    // --- Variabel Data ---
    const semuaKelas = @json($kelas); 
    let siswaTinggal = new Set(); // Menyimpan ID siswa yang tinggal kelas
    let siswaNaikPerKelas = {}; // Menyimpan { 'id_kelas_tujuan': Set([id_siswa, ...]), ... }


    // =================================================================================
    // FUNGSI KRITIS: MEMBUAT HIDDEN INPUT UNTUK SEMUA SISWA YANG DICENTANG
    // =================================================================================
    const updateHiddenInputs = () => {
        if (hiddenContainer) {
            hiddenContainer.innerHTML = ''; // Kosongkan container
            
            // Loop melalui semua kelas tujuan yang sudah ada alokasi siswa
            for (const [kelasId, setSiswa] of Object.entries(siswaNaikPerKelas)) {
                setSiswa.forEach(idSiswa => {
                    // Buat input tersembunyi untuk SETIAP siswa yang dicentang
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    // Nama input harus sesuai dengan array yang diterima controller
                    input.name = `naik_kelas[${kelasId}][]`; 
                    input.value = idSiswa;
                    hiddenContainer.appendChild(input);
                });
            }
        }
    };


    // =================================================================================
    // 1. FUNGSI PEMUATAN KELAS ASAL
    // =================================================================================
    const loadKelasAsal = (idJenjang = '') => {
        let url = `/siswa/kelasDenganSiswa/${idTahunSebelumnya}`;
        if(idJenjang) url += `?jenjang=${idJenjang}`;

        fetch(url)
        .then(res => res.json())
        .then(data => {
            kelasAsal.innerHTML = '<option value="">-- Pilih Kelas Asal --</option>';
            data.forEach(k => {
                const opt = document.createElement('option');
                opt.value = k.id;
                opt.dataset.tingkat = k.tingkat;
                opt.textContent = `${k.tingkat} ${k.nama_kelas}`;
                kelasAsal.appendChild(opt);
            });
        })
        .catch(error => {
            console.error('Error loading kelas asal:', error);
            kelasAsal.innerHTML = '<option value="">-- Gagal memuat kelas --</option>';
        });
    };
    loadKelasAsal();

    // =================================================================================
    // 2. LISTENER PERUBAHAN JENJANG
    // =================================================================================
    document.getElementById('jenjang').addEventListener('change', function() {
        loadKelasAsal(this.value);
        // Reset tampilan
        daftarTinggal.innerHTML = '';
        document.getElementById('daftar_siswa_tinggal').style.display = 'none';
        daftarNaik.innerHTML = '';
        document.getElementById('daftar_siswa_naik').style.display = 'none';
        siswaTinggal.clear();
        siswaNaikPerKelas = {}; 
        updateHiddenInputs(); // PENTING: Kosongkan hidden inputs
        
        tingkatTujuan.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
        kelasTujuan.innerHTML = '<option value="">-- Pilih Kelas Sesuai Tingkat --</option>';
    });

    // =================================================================================
    // 3. LISTENER PERUBAHAN KELAS ASAL (Memuat Daftar Tinggal Kelas & Tingkat Tujuan)
    // =================================================================================
    kelasAsal.addEventListener('change', function(){
        const idKelas = this.value;
        if(!idKelas) {
            document.getElementById('daftar_siswa_tinggal').style.display = 'none';
            document.getElementById('daftar_siswa_naik').style.display = 'none';
            return;
        }

        // --- A. Reset dan Set Tingkat Tujuan ---
        tingkatTujuan.innerHTML = '<option value="">-- Pilih Tingkat --</option>';
        kelasTujuan.innerHTML = '<option value="">-- Pilih Kelas Sesuai Tingkat --</option>';

        const tingkatAsalVal = parseInt(kelasAsal.selectedOptions[0]?.dataset.tingkat) || 0;
        const nextTingkat = tingkatAsalVal + 1;
        siswaTinggal.clear(); 
        siswaNaikPerKelas = {}; 
        updateHiddenInputs(); // PENTING: Reset semua alokasi siswa naik

        // Cek dan tampilkan tingkat berikutnya yang valid
        const tingkatUnik = [...new Set(semuaKelas.map(k => k.tingkat))];
        if(tingkatUnik.includes(nextTingkat)){
            const opt = document.createElement('option');
            opt.value = nextTingkat;
            opt.textContent = nextTingkat;
            tingkatTujuan.appendChild(opt);
        }

        // --- B. Load Daftar Siswa untuk Tinggal Kelas ---
        fetch(`/siswa/daftarSiswa/${idKelas}?tahun=${idTahunSebelumnya}`)
            .then(res => res.json())
            .then(data => {
                daftarTinggal.innerHTML = '';
                if(data.length === 0){
                    daftarTinggal.innerHTML = '<li class="list-group-item text-muted">Tidak ada siswa di kelas ini pada tahun ajaran sebelumnya.</li>';
                } else {
                    document.getElementById('peringatan_tinggal').style.display = 'inline';
                    data.forEach(s => {
                        const sIdString = s.id.toString();
                        const isChecked = siswaTinggal.has(sIdString); 
                        
                        const li = document.createElement('li');
                        li.classList.add('list-group-item');
                        li.innerHTML = `
                            <input type="checkbox" name="tinggal_kelas[]" value="${s.id}" class="form-check-input me-2" ${isChecked ? 'checked' : ''}>
                            ${s.nama_siswa}
                        `;
                        daftarTinggal.appendChild(li);
                    });
                }
                document.getElementById('daftar_siswa_tinggal').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading daftar siswa:', error);
                daftarTinggal.innerHTML = '<li class="list-group-item text-danger">Gagal memuat daftar siswa.</li>';
            });
    });

    // =================================================================================
    // 4. LISTENER UPDATE SISWA TINGGAL KELAS
    // =================================================================================
    // Update siswaTinggal Set saat checkbox per siswa diubah
    daftarTinggal.addEventListener('change', function(e){
        if(e.target.matches('input[type="checkbox"]')){
            if(e.target.checked) siswaTinggal.add(e.target.value);
            else siswaTinggal.delete(e.target.value);
        }
        // PENTING: Refresh daftar siswa naik agar siswa yang baru ditandai tinggal kelas HILANG dari daftar naik
        kelasTujuan.dispatchEvent(new Event('change'));
    });

    // Cek semua tinggal kelas
    cekSemuaTinggal.addEventListener('change', function(){
        daftarTinggal.querySelectorAll('input[type="checkbox"]').forEach(chk=>{
            chk.checked = this.checked;
            if(this.checked) siswaTinggal.add(chk.value);
            else siswaTinggal.delete(chk.value);
        });
        // PENTING: Refresh daftar siswa naik
        kelasTujuan.dispatchEvent(new Event('change'));
    });
    
    // =================================================================================
    // 5. LISTENER PERUBAHAN TINGKAT TUJUAN
    // =================================================================================
    tingkatTujuan.addEventListener('change', function() {
        const tingkatTujuanVal = parseInt(this.value);
        const tingkatAsalVal = parseInt(kelasAsal.selectedOptions[0]?.dataset.tingkat) || 0;

        kelasTujuan.innerHTML = '<option value="">-- Pilih Kelas Sesuai Tingkat --</option>';
        daftarNaik.innerHTML = '';
        document.getElementById('daftar_siswa_naik').style.display = 'none';

        if(!tingkatTujuanVal) return;

        // Validasi Tingkat
        if(tingkatTujuanVal <= tingkatAsalVal){
            alert('Tingkat tujuan harus lebih tinggi dari kelas asal!');
            this.value = '';
            return;
        }

        // Filter dan tampilkan kelas tujuan
        semuaKelas.filter(k => k.tingkat == tingkatTujuanVal).forEach(k => {
            const opt = document.createElement('option');
            opt.value = k.id;
            opt.textContent = k.nama_kelas;
            kelasTujuan.appendChild(opt);
        });
        
        // PENTING: Refresh daftar siswa naik setelah memilih tingkat tujuan
        kelasTujuan.dispatchEvent(new Event('change'));
    });


    // =================================================================================
    // 6. LISTENER PERUBAHAN KELAS TUJUAN (Memuat Daftar Siswa Naik Kelas)
    // =================================================================================
    kelasTujuan.addEventListener('change', function(){
        const idKelasTujuan = this.value;
        const idKelasAsal = kelasAsal.value;
        
        // 1. Panggil fungsi update hidden inputs agar semua alokasi terkirim
        updateHiddenInputs(); // KRUSIAL!

        // 2. Validasi
        if(!idKelasTujuan || !idKelasAsal) {
            daftarNaik.innerHTML = '';
            document.getElementById('daftar_siswa_naik').style.display = 'none';
            return;
        }

        // 3. Inisialisasi Set untuk kelas tujuan yang dipilih
        if(!siswaNaikPerKelas[idKelasTujuan]) {
            siswaNaikPerKelas[idKelasTujuan] = new Set();
        }

        // 4. Ambil daftar siswa dari Kelas Asal (yang akan dipindahkan)
        fetch(`/siswa/daftarSiswa/${idKelasAsal}?tahun=${idTahunSebelumnya}`)
            .then(res => res.json())
            .then(data => {
                daftarNaik.innerHTML = '';

                // 5. Logika Filter: Siswa yang lolos untuk ditampilkan
                const siswaBisaNaik = data.filter(s => {
                    const sIdString = s.id.toString();
                    
                    // Kriteria 1: Tidak Tinggal Kelas
                    if (siswaTinggal.has(sIdString)) return false; 
                    
                    // Kriteria 2: Belum dicentang di KELAS TUJUAN LAIN
                    let sudahDiKelasLain = false;
                    for (const [kelasId, setSiswa] of Object.entries(siswaNaikPerKelas)) {
                        // Cek di Set kelas tujuan lain
                        if (kelasId !== idKelasTujuan.toString() && setSiswa.has(sIdString)) {
                            sudahDiKelasLain = true;
                            break;
                        }
                    }
                    // Jika sudah dialokasikan ke kelas lain, siswa tidak boleh muncul di daftar ini
                    if (sudahDiKelasLain) return false;

                    return true;
                });

                // 6. Tampilkan daftar siswa yang lolos filter
                if(siswaBisaNaik.length === 0){
                    daftarNaik.innerHTML = '<li class="list-group-item text-muted">Semua siswa di kelas ini sudah dialokasikan ke kelas tujuan, tinggal kelas, atau tidak ada siswa tersisa.</li>';
                } else {
                    siswaBisaNaik.forEach(s => {
                        const sIdString = s.id.toString();
                        
                        // Cek apakah siswa sudah dicentang untuk kelas tujuan INI sebelumnya
                        const isChecked = siswaNaikPerKelas[idKelasTujuan].has(sIdString);

                        const li = document.createElement('li');
                        li.classList.add('list-group-item');
                        li.innerHTML = `
                            <input 
                                type="checkbox" 
                                data-kelas-tujuan="${idKelasTujuan}"
                                value="${s.id}" 
                                class="form-check-input me-2"
                                ${isChecked ? 'checked' : ''} 
                            >
                            ${s.nama_siswa}
                        `;
                        // CATATAN: Hapus properti 'name' dari checkbox di sini. 
                        // Data dikirim melalui updateHiddenInputs().

                        // 7. Update Set saat checkbox diubah
                        const checkbox = li.querySelector('input[type="checkbox"]');
                        checkbox.addEventListener('change', function(){
                            if(this.checked) siswaNaikPerKelas[idKelasTujuan].add(sIdString);
                            else siswaNaikPerKelas[idKelasTujuan].delete(sIdString);
                            
                            // PENTING: Panggil updateHiddenInputs setelah perubahan
                            updateHiddenInputs(); 
                        });

                        daftarNaik.appendChild(li);
                    });
                }

                document.getElementById('daftar_siswa_naik').style.display = 'block';
            })
            .catch(error => {
                console.error('Error loading daftar siswa untuk naik kelas:', error);
                daftarNaik.innerHTML = '<li class="list-group-item text-danger">Gagal memuat daftar siswa.</li>';
            });
    });

});
</script>
@endsection
