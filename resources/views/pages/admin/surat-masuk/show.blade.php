@extends('layouts.admin')

@section('title')
    Detail Surat Masuk
@endsection

@section('container')
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp
    <main>
        <header class="page-header page-header-dark bg-gradient-primary-to-secondary pb-10">
            <div class="container-xl px-4">
                <div class="page-header-content pt-4">
                    <div class="row align-items-center justify-content-between">
                        <div class="col-auto mt-4">
                            <h1 class="page-header-title">
                                <div class="page-header-icon">
                                    <i data-feather="file-text"></i>
                                </div>
                                Detail Surat Masuk
                            </h1>
                            <div class="page-header-subtitle">Informasi lengkap surat masuk</div>
                        </div>
                        <div class="col-12 col-xl-auto mt-4">
                            <a class="btn btn-sm btn-light text-primary"
                                href="{{ Session('user')['role'] == 'admin' ? url('/admin/surat-masuk') : url('/staff/surat-masuk') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali Ke Semua Surat
                            </a>
                        </div>
                    </div>

                    <nav class="mt-4 rounded" aria-label="breadcrumb">
                        <ol class="breadcrumb px-3 py-2 rounded mb-0">
                            <li class="breadcrumb-item"><a href="{{ route('admin-dashboard') }}">Dashboard</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('surat-masuk.index') }}">Surat Masuk</a></li>
                            <li class="breadcrumb-item active" aria-current="page">Detail</li>
                        </ol>
                    </nav>
                </div>
            </div>
        </header>


        <!-- Main page content -->
        <div class="container-xl px-4 mt-n10">
            <div class="row gx-4">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div>Detail Surat</div>
                                <div>
                                    @php
                                        $role = Session('user')['role'];
                                        $url = match ($role) {
                                            'admin' => url('/admin/surat-masuk/' . $surat->id . '/download'),
                                            'kepala desa' => url(
                                                '/kepala-desa/surat-masuk/' . $surat->id . '/download',
                                            ),
                                            default => url('/staff/surat-masuk/' . $surat->id . '/download'),
                                        };
                                    @endphp

                                    {{-- <a href="{{ $url }}" class="btn btn-sm btn-outline-primary ms-3"
                                        target="_blank">
                                        <i class="fa fa-download mr-3"></i>&nbsp; Download
                                    </a> --}}
                                    @if (Session('user')['role'] == 'kepala desa')
                                        <a href="{{ $url }}" class="btn btn-sm btn-outline-primary ms-3"
                                            target="_blank">
                                            <i class="fa fa-download mr-3"> </i>&nbsp; Preview PDF
                                        </a>
                                    @else
                                        <a href="{{ $url }}" class="btn btn-sm btn-outline-primary ms-3"
                                            target="_blank">
                                            <i class="fa fa-download mr-3"> </i>&nbsp; Download
                                        </a>
                                    @endif

                                    @if (Session('user')['role'] == 'kepala desa')
                                        @if ($surat->status == 'Pending')
                                            <a href="{{ route('surat-masuk.approve', $surat->id) }}"
                                                class="btn btn-sm btn-success">
                                                <i class="fa fa-check" aria-hidden="true"></i> &nbsp; Sudah Dibaca
                                            </a>
                                            {{-- <a href="{{ route('surat-masuk.reject', $surat->id) }}"
                                                class="btn btn-sm btn-danger">
                                                <i class="fa fa-times" aria-hidden="true"></i> &nbsp; Tolak
                                            </a> --}}
                                        @elseif($surat->status == 'Diterima')
                                            <span class=" btn-sm btn-success text-capitalize">
                                                Surat Telah {{ $surat->status }}
                                            </span>
                                        @else
                                            <span class=" btn-sm btn-danger text-capitalize">
                                                Surat Telah {{ $surat->status }}
                                            </span>
                                        @endif
                                    @endif

                                </div>
                            </div>

                        </div>

                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Jenis Surat</th>
                                        <td class="text-capitalize">
                                            {{ $surat->jenisSurat->nama ? $surat->jenisSurat->nama : '-' }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nomor Surat</th>
                                        <td>{{ $surat->nomor_surat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Surat</th>
                                        <td>{{ \Carbon\Carbon::parse($surat->tanggal_surat)->translatedFormat('d F Y') }}
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Nama Surat</th>
                                        <td>{{ $surat->nama_surat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        @if (Session('user')['role'] == 'kepala desa')
                                            @if ($surat->status == 'Pending')
                                                <td class="text-warning">{{ $surat->status }}</td>
                                            @elseif ($surat->status == 'Diterima')
                                                <td class="text-success">Terverifikasi</td>
                                            @endif
                                        @else
                                            <td>{{ $surat->status }}</td>
                                        @endif
                                    </tr>

                                    <!-- Field Dinamis -->
                                    @foreach ($surat->fieldValues as $value)
                                        <tr>
                                            <th>{{ $value->fieldDefinition->label }}</th>
                                            <td>
                                                @php
                                                    $tipeInput = $value->fieldDefinition->tipe_input;
                                                    $isFile = $tipeInput === 'file';
                                                    $fileUrl = $isFile ? asset('/public/' . $value->value) : null;

                                                    // Cek apakah value kemungkinan adalah tanggal
                                                    try {
                                                        $parsedDate = \Carbon\Carbon::createFromFormat(
                                                            'Y-m-d',
                                                            $value->value,
                                                        );
                                                        $isDate =
                                                            $parsedDate &&
                                                            $parsedDate->format('Y-m-d') === $value->value;
                                                    } catch (\Exception $e) {
                                                        $isDate = false;
                                                    }
                                                @endphp

                                                @if ($isFile)
                                                    <a href="{{ $fileUrl }}" target="_blank"
                                                        class="btn btn-sm btn-outline-primary">
                                                        Lihat File {{ $value->fieldDefinition->label }}
                                                    </a>
                                                @elseif ($isDate)
                                                    {{ $parsedDate->translatedFormat('d F Y') }}
                                                @else
                                                    {{ $value->value }}
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card mb-4">
                        <div class="card-header">
                            Lampiran Surat
                        </div>
                        <div class="card-body">
                            @if ($surat->file_lampiran)
                                @php
                                    $ext = strtolower(pathinfo($surat->file_lampiran, PATHINFO_EXTENSION));
                                    // $fileUrl = url('public/storage/' . $surat->file_lampiran);
                                    $fileUrl = asset('/public/' . $surat->file_lampiran);

                                @endphp

                                @if (in_array($ext, ['pdf']))
                                    <embed src="{{ $fileUrl }}" width="100%" height="375" type="application/pdf">
                                @elseif (in_array($ext, ['jpg', 'jpeg', 'png']))
                                    <img src="{{ $fileUrl }}" class="img-fluid rounded" alt="Lampiran Surat">
                                @else
                                    <p>File tidak dapat ditampilkan. <a href="{{ $fileUrl }}" target="_blank"
                                            class="btn btn-sm btn-primary">Download</a></p>
                                @endif
                            @else
                                <p>Tidak ada file lampiran.</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
@endsection
