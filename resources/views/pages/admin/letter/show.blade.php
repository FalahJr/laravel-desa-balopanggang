@extends('layouts.admin')

@section('title')
    Detail Surat
@endsection

@section('container')
    <main>
        <header class="page-header page-header-compact page-header-light border-bottom bg-white mb-4">
            <div class="container-fluid px-4">
                <div class="page-header-content">
                    <div class="row align-items-center justify-content-between pt-3">
                        <div class="col-auto mb-3">
                            <h1 class="page-header-title">
                                <div class="page-header-icon"><i data-feather="file-text"></i></div>
                                Detail Surat
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mb-3">
                            <a class="btn btn-sm btn-light text-primary" href="{{ route('surat-masuk') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali Ke Semua Surat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- Main page content-->
        <div class="container-fluid px-4">
            <div class="row gx-4">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>Detail Surat
                            </div>
                            <div class="d-flex gap-2">
                                @if ($item->status == 'diproses')
                                    <a href="{{ route('approve', $item->id) }}" class="btn btn-sm btn-success">
                                        <i class="fa fa-check" aria-hidden="true"></i> &nbsp; Setujui
                                    </a>
                                    <a href="{{ route('reject', $item->id) }}" class="btn btn-sm btn-danger">
                                        <i class="fa fa-times" aria-hidden="true"></i> &nbsp; Tolak
                                    </a>
                                @else
                                    <span class="btn btn-sm btn-info text-capitalize">
                                        Surat Telah {{ $item->status }}
                                    </span>
                                @endif

                            </div>
                        </div>
                        <div class="card-body">
                            <div class="mb-3 row">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th>Jenis Surat</th>
                                            <td>{{ $item->letter_type }}</td>
                                        </tr>
                                        <tr>
                                            <th>Nomor Surat</th>
                                            <td>{{ $item->letter_no }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Surat</th>
                                            <td>{{ $item->letter_date }}</td>
                                        </tr>
                                        <tr>
                                            <th>Tanggal Diterima</th>
                                            <td>{{ $item->date_received }}</td>
                                        </tr>
                                        <tr>
                                            <th>Perihal</th>
                                            <td>{{ $item->regarding }}</td>
                                        </tr>
                                        <tr>
                                            <th>Pengirim Surat</th>
                                            <td>{{ $item->sender->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Departemen</th>
                                            <td>{{ $item->department->name }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header">
                            File Surat -
                            <a href="{{ Session('user')['role'] == 'admin'
                                ? route('download-surat-admin', $item->id)
                                : (Session('user')['role'] == 'guru'
                                    ? route('download-surat-guru', $item->id)
                                    : (Session('user')['role'] == 'staff administrasi'
                                        ? route('download-surat-staff', $item->id)
                                        : route('download-surat-kepsek', $item->id))) }}"
                                class="btn btn-sm btn-primary">
                                <i class="fa fa-download" aria-hidden="true"></i> &nbsp; Download Surat
                            </a>
                        </div>

                        <div class="card-body">
                            <div class="mb-3 row">
                                <embed src="{{ Storage::url($item->letter_file) }}" width="500" height="375"
                                    type="application/pdf">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
