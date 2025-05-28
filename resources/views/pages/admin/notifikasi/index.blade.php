@extends('layouts.admin')

@section('title')
    Semua Notifikasi
@endsection

@section('container')
    @php
        use Carbon\Carbon;
        Carbon::setLocale('id');
    @endphp

    <main>
        <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="bell"></i></div>
                                Daftar Notifikasi
                            </h1>
                            <div class="page-header-subtitle">Semua notifikasi terkait surat masuk & keluar</div>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <a class="btn btn-sm btn-light text-primary" href="{{ route('admin-dashboard') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali ke Dashboard
                            </a>
                        </div>
                    </div>

                    <nav class="mt-4 rounded" aria-label="breadcrumb">
                        <ol class="breadcrumb px-3 py-2 rounded mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin-dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Notifikasi</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </header>

        <div class="container-xl px-4 mt-n10">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <span>Notifikasi Terbaru</span>
                    <form action="{{ route('notifikasi.markAllRead') }}" method="POST"
                        onsubmit="return confirm('Tandai semua notifikasi sebagai sudah dibaca?')">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-secondary">Tandai semua telah dibaca</button>
                    </form>
                </div>
                <div class="card-body">
                    @if ($notifikasi->count() > 0)
                        <div class="list-group">
                            @foreach ($notifikasi as $notif)
                                @php
                                    $prefix = str_replace(' ', '-', strtolower(session('user.role')));
                                    $url = '#';

                                    if ($notif->surat->tipe_surat === 'masuk') {
                                        $url = url("$prefix/surat-masuk/" . $notif->surat_id);
                                    } elseif ($notif->surat->tipe_surat === 'keluar') {
                                        $url = url("$prefix/surat-keluar/" . $notif->surat_id);
                                    }
                                @endphp
                                <a href="{{ $url }}"
                                    class="list-group-item list-group-item-action d-flex justify-content-between align-items-center {{ $notif->is_seen == 'N' ? 'bg-light fw-bold' : '' }}">
                                    <div>
                                        <div class="mb-1">{{ $notif->judul }}</div>
                                        <small
                                            class="text-muted">{{ Carbon::parse($notif->created_at)->diffForHumans() }}</small>
                                    </div>
                                    <i data-feather="chevron-right" class="text-muted"></i>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="alert alert-info mb-0">
                            Tidak ada notifikasi untuk ditampilkan.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endsection
