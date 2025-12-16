<aside class="sidebar sidebar-default sidebar-white sidebar-base navs-rounded-all " id="main-sidebar">
<div class="sidebar-toggle d-flex align-items-center justify-content-center"
     data-toggle="sidebar" 
     data-active="true"
     style="
        cursor: pointer;
        z-index: 1050;
        position: absolute;
        top: 10px;
        right: -25px;
        width: 40px;            
        height: 40px;         
        background-color: #ffffff; 
        border-radius: 50%;     
        box-shadow: 0 2px 6px rgba(0,0,0,0.15); 
     ">
    <i class="icon ri-menu-fold-line text-primary" style="font-size: 1.8rem; line-height: 1;"></i>
</div>
    <div class="sidebar-body pt-0 data-scrollbar">
        <div class="sidebar-list">
            <ul class="navbar-nav iq-main-menu" id="sidebar-menu">
                <li class="nav-item static-item">
                    <a class="nav-link static-item disabled" href="#" tabindex="-1">
                        <span class="default-icon">Home</span>
                        <span class="mini-icon">-</span>
                    </a>
                </li>

                {{-- PERBAIKAN DASHBOARD ROUTE & ACTIVE STATE --}}
                {{-- <li class="nav-item">
                    @php
                        $isAdmin = auth()->user()->hasAnyRole(['Admin', 'Bendahara', 'Super Administrator']);
                        $dashboardRoute = $isAdmin ? route('admin.dashboard') : route('wali.dashboard'); 
                        
                        $isActive = request()->routeIs('admin.dashboard') || request()->routeIs('wali.dashboard');
                                            
                    @endphp --}}

                    {{-- PERBAIKAN DASHBOARD ROUTE & ACTIVE STATE --}}
                <li class="nav-item">
                    @php
                        // Inisialisasi default agar tidak error jika tidak login
                        $isAdmin = false;
                        $dashboardRoute = '#'; // Tautkan ke halaman default yang aman
                        
                        // HANYA JALANKAN LOGIKA ROLE JIKA USER SUDAH LOGIN
                        if (auth()->check()) {
                            // Tentukan rute tujuan berdasarkan role
                            $isAdmin = auth()->user()->hasAnyRole(['Admin', 'Bendahara', 'Super Administrator']);
                            $dashboardRoute = $isAdmin ? route('admin.dashboard') : route('wali.dashboard'); 
                        }
                        
                        // Tentukan kondisi aktif (ini tidak perlu auth()->check())
                        $isActive = request()->routeIs('admin.dashboard') || request()->routeIs('wali.dashboard');
                    @endphp

                    <a class="nav-link {{ $isActive ? 'active' : '' }}" aria-current="page" href="{{ $dashboardRoute }}">
                        <i class="icon">
                            <svg width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="icon-20">
                                <path opacity="0.4" d="M16.0756 2H19.4616C20.8639 2 22.0001 3.14585 22.0001 4.55996V7.97452C22.0001 9.38864 20.8639 10.5345 19.4616 10.5345H16.0756C14.6734 10.5345 13.5371 9.38864 13.5371 7.97452V4.55996C13.5371 3.14585 14.6734 2 16.0756 2Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M4.53852 2H7.92449C9.32676 2 10.463 3.14585 10.463 4.55996V7.97452C10.463 9.38864 9.32676 10.5345 7.92449 10.5345H4.53852C3.13626 10.5345 2 9.38864 2 7.97452V4.55996C2 3.14585 3.13626 2 4.53852 2ZM4.53852 13.4655H7.92449C9.32676 13.4655 10.463 14.6114 10.463 16.0255V19.44C10.463 20.8532 9.32676 22 7.92449 22H4.53852C3.13626 22 2 20.8532 2 19.44V16.0255C2 14.6114 3.13626 13.4655 4.53852 13.4655ZM19.4615 13.4655H16.0755C14.6732 13.4655 13.537 14.6114 13.537 16.0255V19.44C13.537 20.8532 14.6732 22 16.0755 22H19.4615C20.8637 22 22 20.8532 22 19.44V16.0255C22 14.6114 20.8637 13.4655 19.4615 13.4655Z" fill="currentColor"></path>
                            </svg>
                        </i>
                        <span class="item-name">Dashboard</span>
                    </a>
                </li>
                
                <li><hr class="hr-horizontal"></li>
                
                {{-- Cek minimal satu permission di MASTER DATA sebelum menampilkan judul grup --}}
                {{-- @if(auth()->user()->can('jenjang.index') || auth()->user()->can('tahun-ajaran.index') || auth()->user()->can('kelas.index') || auth()->user()->can('guru.index') || auth()->user()->can('siswa.index')) --}}
                @if(auth()->check() && (auth()->user()->can('jenjang.index') || auth()->user()->can('tahun-ajaran.index') || auth()->user()->can('kelas.index') || auth()->user()->can('guru.index') || auth()->user()->can('siswa.index')))
                    <li class="nav-item static-item">
                        <a class="nav-link static-item disabled" href="#" tabindex="-1">
                            <span class="default-icon">Master Data</span>
                            <span class="mini-icon">-</span>
                        </a>
                    </li>
                @endif

                {{-- Grup MASTER DATA --}}
                {{-- Cek minimal satu permission di grup ini agar link induk terlihat --}}
                {{-- @if(auth()->user()->can('jenjang.index') || auth()->user()->can('tahun-ajaran.index') || auth()->user()->can('kelas.index') || auth()->user()->can('guru.index') || auth()->user()->can('siswa.index')) --}}
                @if(auth()->check() && (auth()->user()->can('jenjang.index') || auth()->user()->can('tahun-ajaran.index') || auth()->user()->can('kelas.index') || auth()->user()->can('guru.index') || auth()->user()->can('siswa.index')))
                    <li class="nav-item">
                    <a class="nav-link collapsed" data-bs-toggle="collapse" href="#sidebar-master" role="button" aria-expanded="false" aria-controls="sidebar-master">
                        <i class="icon">
                            <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/>
                            </svg>
                        </i>
                        <span class="item-name">Data Sekolah</span>
                        <i class="right-icon">
                            <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </i>
                    </a>
                    <ul class="sub-nav collapse" id="sidebar-master" data-bs-parent="#sidebar-menu">
                        {{-- START: Tambahan untuk Pendaftar Baru (Calon Siswa) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('pendaftar.index') ? 'active' : '' }}" href="{{ route('pendaftar.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                                </i>
                                <span class="item-name">Calon Siswa (Pendaftar Baru)</span>
                            </a>
                        </li>
                        {{-- END: Tambahan untuk Pendaftar Baru (Calon Siswa) --}}
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('master-jenjang.index') ? 'active' : '' }}" href="{{ route('master-jenjang.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg>
                                </i>
                                <span class="item-name">Jenjang</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('tahun-ajaran.index') ? 'active' : '' }}" href="{{ route('tahun-ajaran.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg>
                                </i>
                                <span class="item-name">Tahun Ajaran</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('kelas.index') ? 'active' : '' }}" href="{{ route('kelas.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg>
                                </i>
                                <span class="item-name">Kelas</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('guru.index') ? 'active' : '' }}" href="{{ route('guru.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg>
                                </i>
                                <span class="item-name">Guru</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('siswa.index') ? 'active' : '' }}" href="{{ route('siswa.index') }}">
                                <i class="icon">
                                    <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg>
                                </i>
                                <span class="item-name">Siswa</span>
                            </a>
                        </li>
                    </ul>
                </li>
                @endif
                
                {{-- Grup Pengaturan --}}
                {{-- @if(auth()->user()->can('jenis-pembayaran.index') || auth()->user()->can('atur-nominal.index') || auth()->user()->can('user.index') || auth()->user()->can('role.index')) --}}
                @if(auth()->check() && (auth()->user()->can('jenis-pembayaran.index') || auth()->user()->can('atur-nominal.index') || auth()->user()->can('user.index') || auth()->user()->can('role.index')))
                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#sidebar-setting" role="button" aria-expanded="false" aria-controls="sidebar-setting">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M12 15a3 3 0 100-6 3 3 0 000 6z" fill="currentColor"/>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M19.407 8.019a1 1 0 01.442-.969l1.722-.995a1 1 0 00.126-1.574l-2.072-3.59a1 1 0 00-1.258-.291l-1.93.999a1 1 0 01-1.096-.282 9.006 9.006 0 00-3.953-2.007 1 1 0 00-1.127.135L8.74 3.791a1 1 0 00-1.42.062l-1.071 1.071a1 1 0 00-.062 1.42L5.858 7.378a1 1 0 01.282 1.096l-.999 1.93a1 1 0 00.291 1.258l3.59 2.072a1 1 0 001.574-.126l.995-1.722a1 1 0 01.969-.442 9.006 9.006 0 004.887 0z" fill="currentColor"/>
                                </svg>
                            </i>
                            <span class="item-name">Pengaturan</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                    <ul class="sub-nav collapse" id="sidebar-setting" data-bs-parent="#sidebar-menu">
                        @if(auth()->check() && auth()->user()->hasRole('Bendahara'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('jenis-pembayaran.index') ? 'active' : '' }}" href="{{ route('jenis-pembayaran.index') }}">
                                    <i class="icon"><svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg></i>
                                    <span class="item-name">Kategori Pembayaran</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('atur-nominal.index') ? 'active' : '' }}" href="{{ route('atur-nominal.index') }}">
                                    <i class="icon"><svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg></i>
                                    <span class="item-name">Nominal Pembayaran</span>
                                </a>
                            </li>
                        @endif
                        @if(auth()->check() && auth()->user()->hasRole('Super Administrator'))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('users.index') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                <i class="icon"><svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg></i>
                                <span class="item-name">Manajemen User</span>
                            </a>
                        </li>
                        @endif

                        {{-- Menu Role & Permission (Gabungan) --}}
                        @if(auth()->check() && auth()->user()->hasRole('Super Administrator'))
                        <li class="nav-item">
                        {{-- Gunakan route 'roles.index' atau buat route baru seperti 'role-permission.index' --}}
                        <a class="nav-link {{ request()->routeIs(['roles.index', 'permissions.index', 'role-permission.index']) ? 'active' : '' }}" href="{{ route('roles.index') }}">
                            <i class="icon">
                            <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8"/></svg>
                            </i>
                            <span class="item-name">Role & Permission</span>
                        </a>
                        </li>
                        @endif
                    </ul>
                </li>
                @endif

                                {{-- Cek minimal satu permission di grup ini agar link induk terlihat --}}
                {{-- @if(auth()->user()->can('tagihan.index') || auth()->user()->can('pembayaran.index')) --}}
                @if(auth()->check() && (auth()->user()->can('tagihan.index') || auth()->user()->can('pembayaran.index')))
                    <li class="nav-item">
                        <a class="nav-link collapsed" data-bs-toggle="collapse" href="#sidebar-transaksi" role="button" aria-expanded="false" aria-controls="sidebar-transaksi">
                            <i class="icon">
                                <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2v-6zm0 8h2v2h-2v-2z" fill="currentColor"/>
                                </svg>
                            </i>
                            <span class="item-name">Transaksi</span>
                            <i class="right-icon">
                                <svg class="icon-18" xmlns="http://www.w3.org/2000/svg" width="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </i>
                        </a>
                    <ul class="sub-nav collapse" id="sidebar-transaksi" data-bs-parent="#sidebar-menu">
                        
                    <li class="nav-item">
                        {{-- <a class="nav-link {{ request()->routeIs('tagihan.index') || request()->routeIs('tagihan.anak') ? 'active' : '' }}" href="{{ auth()->user()->hasRole('Orang Tua') || auth()->user()->hasRole('Siswa') ? route('tagihan.anak') : route('tagihan.index') }}"> --}}
                        <a class="nav-link {{ request()->routeIs('tagihan.index') || request()->routeIs('tagihan.anak') ? 'active' : '' }}" href="{{ auth()->user()?->hasRole('Orang Tua') || auth()->user()?->hasRole('Siswa') ? route('tagihan.anak') : route('tagihan.index') }}">
                            <i class="icon">
                                <svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="12" cy="12" r="8"></circle>
                                </svg>
                            </i>
                            {{-- <span class="item-name">
                                {{ auth()->user()->hasRole('Orang Tua') || auth()->user()->hasRole('Siswa') ? 'Tagihan Anak' : 'Data Tagihan' }}
                            </span> --}}
                            <span class="item-name">
                                {{ auth()->user()?->hasRole('Orang Tua') || auth()->user()?->hasRole('Siswa') ? 'Tagihan Anak' : 'Data Tagihan' }}
                            </span>
                        </a>
                    </li>
                        
                        {{-- Menu Riwayat Pembayaran (Tetap Menggunakan Pembayaran.index untuk sementara) --}}
                        {{-- Catatan: Jika ada pemisahan pembayaran anak/admin, rute ini juga perlu diperbaiki --}}
                        @if(auth()->check() && auth()->user()->hasRole('Bendahara'))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('pembayaran.index') ? 'active' : '' }}" href="{{ route('pembayaran.index') }}">
                                    <i class="icon"><svg class="icon-10" xmlns="http://www.w3.org/2000/svg" width="10" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="8" fill="currentColor"></circle></svg></i>
                                    <span class="item-name">Data Pembayaran</span>
                                </a>
                            </li>
                        @endif                       
                    </ul>
                </li>
                @endif

                {{-- menu notifikasi log --}}
                {{-- @can('notifikasi-log.index')
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('notifikasi-log.index') ? 'active' : '' }}" href="{{ route('notifikasi-log.index') }}">
                        <i class="icon">
                            <svg class="icon-20" width="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z" fill="currentColor"/>
                            </svg>
                        </i>
                        <span class="item-name">Notifikasi Log</span>
                    </a>
                </li>
                @endcan --}}
            </ul>
        </div>
    </div>
    <div class="sidebar-footer"></div>
</aside>