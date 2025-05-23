@extends('layouts.admin')

@section('title')
    Detail Surat Masuk
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
                                Detail Surat Masuk
                            </h1>
                        </div>
                        <div class="col-12 col-xl-auto mb-3">
                            <a class="btn btn-sm btn-light text-primary" href="{{ route('surat-masuk.index') }}">
                                <i class="me-1" data-feather="arrow-left"></i>
                                Kembali Ke Semua Surat
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main page content -->
        <div class="container-fluid px-4">
            <div class="row gx-4">
                <div class="col-lg-7">
                    <div class="card mb-4">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div>Detail Surat</div>
                            <div class="d-flex gap-2">
                                @if ($surat->status == 'Pending')
                                    {{-- <a href="{{ route('surat-masuk.approve', $surat->id) }}" class="btn btn-sm btn-success">
                                        <i class="fa fa-check" aria-hidden="true"></i> &nbsp; Setujui
                                    </a>
                                    <a href="{{ route('surat-masuk.reject', $surat->id) }}" class="btn btn-sm btn-danger">
                                        <i class="fa fa-times" aria-hidden="true"></i> &nbsp; Tolak
                                    </a> --}}
                                @else
                                    <span class="btn btn-sm btn-info text-capitalize">
                                        Surat Telah {{ $surat->status }}
                                    </span>
                                @endif
                            </div>
                        </div>

                        <div class="card-body">
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <th>Jenis Surat</th>
                                        <td class="text-capitalize">
                                            {{ $surat->jenisSurat->nama ? $surat->jenisSurat->nama : '-' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nomor Surat</th>
                                        <td>{{ $surat->nomor_surat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Tanggal Surat</th>
                                        <td>{{ \Carbon\Carbon::parse($surat->tanggal_surat)->format('d-m-Y') }}</td>
                                    </tr>
                                    <tr>
                                        <th>Nama Surat</th>
                                        <td>{{ $surat->nama_surat }}</td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td>{{ $surat->status }}</td>
                                    </tr>

                                    <!-- Field Dinamis -->
                                    @foreach ($surat->fieldValues as $value)
                                        <tr>
                                            <th>{{ $value->fieldDefinition->label }}</th>
                                            <td>{{ $value->value }}</td>
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
                            @if ($surat->file_lampiran)
                                <a href="{{ asset('storage/' . $surat->file_lampiran) }}"
                                    class="btn btn-sm btn-primary float-end" download>
                                    <i class="fa fa-download" aria-hidden="true"></i> &nbsp; Download Surat
                                </a>
                            @endif
                        </div>
                        <div class="card-body">
                            @if ($surat->file_lampiran)
                                @php
                                    $ext = strtolower(pathinfo($surat->file_lampiran, PATHINFO_EXTENSION));
                                    $fileUrl = url('public/storage/' . $surat->file_lampiran);
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
