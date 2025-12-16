@extends('layouts.app')
@section('title','Edit Tagihan')
@section('content')
<div class="row justify-content-center">
  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h5>Edit Tagihan</h5></div>
      <div class="card-body">
        @include('layouts.alerts')
        <form action="{{ route('tagihan.update', $tagihan) }}" method="POST">
          @csrf @method('PUT')
          <div class="mb-3">
            <label>Siswa</label>
            <select name="id_siswa" class="form-control">
              @foreach($siswas as $s)
                <option value="{{ $s->id }}" {{ $s->id == $tagihan->id_siswa ? 'selected' : '' }}>
                  {{ $s->nama }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label>Jenis Pembayaran</label>
            <select name="id_jenis_pembayaran" class="form-control">
              @foreach($jenisPembayaran as $jp)
                <option value="{{ $jp->id }}" {{ $jp->id == $tagihan->id_jenis_pembayaran ? 'selected' : '' }}>
                  {{ $jp->nama_jenis }}
                </option>
              @endforeach
            </select>
          </div>
          <div class="row">
            <div class="col-md-4 mb-3">
              <label>Bulan</label>
              <select name="bulan_tagihan" class="form-control">
                <option value="">Non Bulanan</option>
                @for($i=1;$i<=12;$i++)
                  <option value="{{ $i }}" {{ $tagihan->bulan_tagihan == $i ? 'selected' : '' }}>
                    {{ \Carbon\Carbon::create()->month($i)->format('F') }}
                  </option>
                @endfor
              </select>
            </div>
            <div class="col-md-4 mb-3">
              <label>Tahun</label>
              <input type="number" name="tahun_tagihan" class="form-control" value="{{ $tagihan->tahun_tagihan }}">
            </div>
            <div class="col-md-4 mb-3">
              <label>Jatuh Tempo</label>
              <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="{{ $tagihan->tanggal_jatuh_tempo ? $tagihan->tanggal_jatuh_tempo->format('Y-m-d') : '' }}">
            </div>
          </div>

          <div class="mb-3">
            <label>Total Tagihan</label>
            <input type="number" name="total_tagihan" class="form-control" value="{{ $tagihan->total_tagihan }}">
          </div>

          <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
              <option value="Belum Bayar" {{ $tagihan->status == 'Belum Bayar' ? 'selected' : '' }}>Belum Bayar</option>
              <option value="Dalam Proses" {{ $tagihan->status == 'Dalam Proses' ? 'selected' : '' }}>Dalam Proses</option>
              <option value="Lunas" {{ $tagihan->status == 'Lunas' ? 'selected' : '' }}>Lunas</option>
            </select>
          </div>

          <div class="d-flex gap-2">
            <a href="{{ route('tagihan.index') }}" class="btn btn-secondary">Batal</a>
            <button class="btn btn-primary">Simpan Perubahan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
