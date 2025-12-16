@extends('layouts.app') 

@section('title', 'Dashboard Admin')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h4>Selamat Datang di Dashboard Admin</h4>
                </div>
                <div class="card-body">
                    <p>Anda telah berhasil masuk sebagai {{ Auth::user()->name }}.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection