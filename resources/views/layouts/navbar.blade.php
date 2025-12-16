<nav class="nav navbar navbar-expand-lg navbar-light iq-navbar">
  <div class="container-fluid navbar-inner">
    <a href="{{ route('dashboard') }}" class="navbar-brand">
        <div class="logo-main">
            <div class="logo-normal">
                <!-- Logo SVG -->
            </div>
            <div class="logo-mini">
                <!-- Mini Logo SVG -->
            </div>
        </div>
        <h4 class="logo-title">Baitul Ilmi Islamic School</h4>
    </a>

    {{-- <div class="input-group search-input">
        <span class="input-group-text" id="search-input">
        </span>
        <input type="search" class="form-control" placeholder="Search...">
    </div> --}}

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
        <span class="navbar-toggler-icon">
            <span class="mt-2 navbar-toggler-bar bar1"></span>
            <span class="navbar-toggler-bar bar2"></span>
            <span class="navbar-toggler-bar bar3"></span>
        </span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="mb-2 navbar-nav ms-auto align-items-center navbar-list mb-lg-0">

            <!-- Notification Dropdown -->
            <li class="nav-item dropdown">
                <a href="#" class="nav-link" id="notification-drop" data-bs-toggle="dropdown">
                    <!-- Notification Icon SVG -->
                    <span class="bg-danger dots"></span>
                </a>
                <div class="p-0 sub-drop dropdown-menu dropdown-menu-end" aria-labelledby="notification-drop">
                    <div class="m-0 shadow-none card">
                        <div class="py-3 card-header d-flex justify-content-between bg-primary">
                            <h5 class="mb-0 text-white">Notifikasi</h5>
                        </div>
                        <div class="p-0 card-body">
                            <a href="#" class="iq-sub-card">
                                <div class="d-flex align-items-center">
                                    <img class="p-1 avatar-40 rounded-pill bg-soft-primary" src="../assets/images/shapes/01.png" alt="">
                                    <div class="ms-3 w-100">
                                        <h6 class="mb-0 ">Hani Putri Pratiwi</h6>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="mb-0">95 MB</p>
                                            <small class="float-end font-size-12">Just Now</small>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- Mail Dropdown -->
            <li class="nav-item dropdown">
                <a href="#" class="nav-link" id="mail-drop" data-bs-toggle="dropdown">
                    <!-- Mail Icon SVG -->
                    <span class="bg-primary count-mail"></span>
                </a>
                <div class="p-0 sub-drop dropdown-menu dropdown-menu-end" aria-labelledby="mail-drop">
                    <div class="m-0 shadow-none card">
                        <div class="py-3 card-header d-flex justify-content-between bg-primary">
                            <h5 class="mb-0 text-white">Pesan</h5>
                        </div>
                        <div class="p-0 card-body ">
                            <a href="#" class="iq-sub-card">
                                <div class="d-flex align-items-center">
                                    <img class="p-1 avatar-40 rounded-pill bg-soft-primary" src="../assets/images/shapes/01.png" alt="">
                                    <div class="ms-3">
                                        <h6 class="mb-0 ">Hani Putri Pratiwi</h6>
                                        <small class="float-start font-size-12">13 Jun</small>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </li>

            <!-- User Dropdown -->
            <li class="nav-item dropdown">
                @auth
                <a class="py-0 nav-link d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                    <img src="{{ Auth::user()->avatar ? asset('storage/avatars/' . Auth::user()->avatar) : asset('assets/images/avatars/01.png') }}" 
                        alt="User-Profile" class="img-fluid avatar avatar-50 avatar-rounded" style="object-fit: cover; width: 50px; height: 50px;">
                    <div class="caption ms-3 d-none d-md-block">
                        <h6 class="mb-0 caption-title">{{ Auth::user()->name }}</h6>
                        <p class="mb-0 caption-sub-title">{{ Auth::user()->getRoleNames()->first() ?? 'User' }}</p>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="{{ route('profile.edit') }}">Profile</a></li>
                    {{-- <li><a class="dropdown-item" href="{{ route('profile.update') }}">Privacy Setting</a></li> --}}
                    <li><hr class="dropdown-divider"></li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">Logout</button>
                    </form>
                </ul>
                @else
                <a href="{{ route('login.wali') }}" class="nav-link">Login</a>
                @endauth
            </li>

        </ul>
    </div>
  </div>
</nav>

<!-- Nav Header Component Start -->
<div class="iq-navbar-header" style="height: 215px;">
    <div class="container-fluid iq-container">
        <div class="row">
            <div class="col-md-12">
                <div class="flex-wrap d-flex justify-content-between align-items-center">
                    <div>
                        <h1>Baitul Ilmi Islamic School</h1>
                        <p>Aplikasi Website Pembayaran Sekolah Digital</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="iq-header-img">
        <img src="../assets/images/dashboard/top-header.png" alt="header" class="theme-color-default-img img-fluid w-100 h-100 animated-scaleX">
        <img src="../assets/images/dashboard/top-header1.png" alt="header" class="theme-color-purple-img img-fluid w-100 h-100 animated-scaleX">
        <img src="../assets/images/dashboard/top-header2.png" alt="header" class="theme-color-blue-img img-fluid w-100 h-100 animated-scaleX">
        <img src="../assets/images/dashboard/top-header3.png" alt="header" class="theme-color-green-img img-fluid w-100 h-100 animated-scaleX">
        <img src="../assets/images/dashboard/top-header4.png" alt="header" class="theme-color-yellow-img img-fluid w-100 h-100 animated-scaleX">
        <img src="../assets/images/dashboard/top-header5.png" alt="header" class="theme-color-pink-img img-fluid w-100 h-100 animated-scaleX">
    </div>
</div>
<!-- Nav Header Component End -->
