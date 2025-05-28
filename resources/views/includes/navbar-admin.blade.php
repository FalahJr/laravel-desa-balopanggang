<nav class="topnav navbar navbar-expand shadow justify-content-between justify-content-sm-start navbar-light"
    style="background-color: #F1E9D2;" id="sidenavAccordion">
    <!-- Sidenav Toggle Button-->
    <button class="btn btn-icon btn-transparent-dark order-1 order-lg-0 me-2 ms-lg-2 me-lg-0" id="sidebarToggle">
        <i data-feather="menu"></i>
    </button>
    <!-- Navbar Brand-->
    <!-- * * Tip * * You can use text or an image for your navbar brand.-->
    <!-- * * * * * * When using an image, we recommend the SVG format.-->
    <!-- * * * * * * Dimensions: Maximum height: 32px, maximum width: 240px-->
    <a class="navbar-brand pe-3 ps-4 ps-lg-2" href="{{ route('admin-dashboard') }}">
        <div class="d-flex align-items-center">
            <img class="navbar-brand-img" src="{{ url('/public/storage/assets/logo-sipraga-gresik.png') }}"
                style=" height: 5vh" alt="Logo" />
            <span class="ms-2 d-none d-lg-block text-dark">SIPRAGA</span>

        </div>
        {{-- Sipraga --}}
    </a>
    <!-- Navbar Search Input-->
    <!-- * * Note: * * Visible only on and above the lg breakpoint-->
    <form class="form-inline me-auto d-none d-lg-block me-3">
        <div class="input-group input-group-joined input-group-solid">

        </div>
    </form>
    <!-- Navbar Items-->
    <ul class="navbar-nav align-items-center ms-auto">
        <!-- Navbar Search Dropdown-->
        <!-- * * Note: * * Visible only below the lg breakpoint-->
        <!-- Notifikasi Dropdown -->
        <!-- Notifikasi -->
        @php
            use App\Models\Notifikasi;

            $unreadNotifCount = Notifikasi::where('role', session('user')['role'])->where('is_seen', 'N')->count();

            $notifikasiList = Notifikasi::where('role', session('user')['role'])
                ->orderBy('created_at', 'desc')
                ->take(4)
                ->get();
        @endphp

        <li class="nav-item dropdown no-caret me-3 me-lg-4">
            <a class="btn btn-icon btn-dark dropdown-toggle position-relative overflow-visible" id="navbarDropdownNotif"
                href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <i data-feather="bell"></i>
                @if ($unreadNotifCount > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger mt-1"
                        style="font-size: 0.6rem; padding: 4px 6px; z-index: 10;">
                        {{ $unreadNotifCount }}
                    </span>
                @endif
            </a>

            <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                aria-labelledby="navbarDropdownNotif">
                <h6 class="dropdown-header d-flex align-items-center">
                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name">Notifikasi</div>
                    </div>
                </h6>
                <div class="dropdown-divider"></div>

                @forelse ($notifikasiList as $notif)
                    {{-- @php
                        $url = '#';
                        if ($notif->surat->tipe_surat == 'masuk') {
                            $url = url('kepala-desa/surat-masuk/' . $notif->surat_id);
                        } elseif ($notif->surat->tipe_surat == 'keluar') {
                            $url = url('kepala-desa/surat-keluar/' . $notif->surat_id);
                        }
                    @endphp --}}
                    <a class="dropdown-item d-flex align-items-center"
                        href="{{ route('notifikasi.baca', ['id' => $notif->id]) }}">
                        <div class="me-3">
                            <div class="icon-circle bg-primary">
                                <i data-feather="file-text" class="text-white"></i>
                            </div>
                        </div>
                        <div>
                            <div class="small text-gray-500">{{ $notif->created_at->format('d M Y H:i') }}</div>
                            <span class="{{ $notif->is_seen == 'N' ? 'fw-bold' : '' }}">
                                {{ $notif->judul }}
                            </span>
                        </div>
                    </a>
                @empty
                    <div class="dropdown-item text-center small text-gray-500">Tidak ada notifikasi</div>
                @endforelse

                <div class="dropdown-divider"></div>
                <a class="dropdown-item text-center small text-gray-500"
                    href="{{ url(str_replace(' ', '-', strtolower(session('user.role'))) . '/notifikasi') }}">
                    Lihat semua notifikasi
                </a>

            </div>
        </li>



        <!-- User Dropdown-->
        <li class="nav-item dropdown no-caret dropdown-user me-3 me-lg-4">
            <a class="btn btn-icon btn-transparent-dark dropdown-toggle" id="navbarDropdownUserImage"
                href="javascript:void(0);" role="button" data-bs-toggle="dropdown" aria-haspopup="true"
                aria-expanded="false">
                @if (Session('user')['profile'] != null)
                    <img class="img-fluid" src="{{ Storage::url(Auth::user()->profile) }}" />
                @else
                    <img class="img-fluid" src="https://ui-avatars.com/api/?name={{ Session('user')['name'] }}" />
                @endif
            </a>
            <div class="dropdown-menu dropdown-menu-end border-0 shadow animated--fade-in-up"
                aria-labelledby="navbarDropdownUserImage">
                <h6 class="dropdown-header d-flex align-items-center">
                    @if (Session('user')['profile'] != null)
                        <img class="dropdown-user-img" src="{{ Storage::url(Session('user')['profile']) }}" />
                    @else
                        <img class="dropdown-user-img"
                            src="https://ui-avatars.com/api/?name={{ Session('user')['name'] }}" />
                    @endif

                    <div class="dropdown-user-details">
                        <div class="dropdown-user-details-name">{{ Session('user')['name'] }}</div>
                        <div class="dropdown-user-details-email">{{ Session('user')['email'] }}</div>
                    </div>
                </h6>
                <div class="dropdown-divider"></div>
                {{-- <a class="dropdown-item"
                    @if (Session('user')['role'] == 'admin') href="{{ url('admin/setting') }}"
                   @elseif (Session('user')['role'] == 'guru')
                   href="{{ url('guru/setting') }}"
                   @elseif (Session('user')['role'] == 'kepala sekolah')
                   href="{{ url('kepala-sekolah/setting') }}"
                   @elseif (Session('user')['role'] == 'staff administrasi')
                   href="{{ url('staff/setting') }}" @endif>
                    <div class="dropdown-item-icon"><i data-feather="settings"></i></div>
                    Account
                </a> --}}
                <form action="{{ url('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="dropdown-item">
                        <div class="dropdown-item-icon"><i data-feather="log-out"></i></div>
                        Logout
                    </button>
                </form>
            </div>
        </li>
    </ul>
</nav>
