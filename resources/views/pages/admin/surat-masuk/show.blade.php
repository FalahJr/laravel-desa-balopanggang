@extends('layouts.admin')

@section('title')
    Detail Surat Masuk
@endsection

@section('container')
    @php
        \Carbon\Carbon::setLocale('id');
    @endphp
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
                            <div class="d-flex align-items-center justify-content-between w-100">
                                <div>Detail Surat</div>
                                <a href="{{ route('surat-masuk.download', $surat->id) }}"
                                    class="btn btn-sm btn-outline-primary ms-3" target="_blank">
                                    <i class="fa fa-download mr-3"> </i>&nbsp; Download
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                @if ($surat->status == 'Pending')
                                    {{-- Tombol approve/reject di masa depan --}}
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
                                        <td>{{ $surat->status }}</td>
                                    </tr>

                                    <!-- Field Dinamis -->
                                    @foreach ($surat->fieldValues as $value)
                                        <tr>
                                            <th>{{ $value->fieldDefinition->label }}</th>
                                            <td>
                                                @php
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

                                                @if ($isDate)
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
