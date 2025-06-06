<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>{{ $surat->jenisSurat->nama }}</title>
    <style>
        body {
            font-family: 'Times New Roman', serif;
            margin: 0;
            padding: 20px;
            font-size: 12pt;
            line-height: 1.5;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
        }

        .title {
            text-align: center;
            font-size: 16pt;
            font-weight: bold;
            text-decoration: underline;
            margin: 30px 0 20px 0;
            text-transform: uppercase;
        }

        .nomor {
            text-align: center;
            font-size: 12pt;
            margin-bottom: 30px;
        }

        .content {
            margin: 20px 0;
            text-align: justify;
        }

        .pembuka {
            margin-bottom: 20px;
        }

        table {
            width: 100%;
            margin-top: 15px;
        }

        td {
            padding: 4px 2px;
        }

        .penutup {
            margin-top: 30px;
            margin-bottom: 40px;
        }

        .signature-section {
            text-align: right;
            margin-top: 40px;
        }

        .signature-date {
            margin-bottom: 10px;
        }

        .signature-title {
            /* margin-bottom: 80px; */
        }

        .signature-name {
            text-decoration: underline;
            font-weight: bold;
        }
    </style>
</head>

<body>

    <!-- Kop Surat -->
    <div class="header">
        <img src="{{ asset('public/assets/kop-surat-3.png') }}" style="max-width: 100%; height: auto;">
    </div>

    <!-- Judul -->
    <div class="title">{{ strtoupper($surat->jenisSurat->nama) }}</div>

    <!-- Nomor -->
    <div class="nomor">
        Nomor : {{ $surat->nomor_surat ?? '........./........./DS-BALONGPANGGANG/VIII/2023' }}
    </div>

    <!-- Isi -->
    <div class="content">
        <div class="pembuka">
            Yang bertanda tangan dibawah ini Kepala Desa Balongpanggang, Kecamatan Balong Panggang,
            Kabupaten Gresik menerangkan bahwa :
        </div>

        <!-- Tabel Data -->
        @if ($fields && $fields->isNotEmpty())
            <table>
                @foreach ($fields as $field)
                    <tr>
                        <td style="width: 30%;">{{ $field['label'] }}</td>
                        <td style="width: 2%;">:</td>
                        <td style="">
                            @php
                                $value = $field['value'];
                                $tipe = $field['tipe_input'];
                                $formattedValue = $value;

                                $isDate = false;
                                if ($tipe === 'date') {
                                    try {
                                        $parsedDate = \Carbon\Carbon::createFromFormat('Y-m-d', $formattedValue);
                                        $isDate = $parsedDate && $parsedDate->format('Y-m-d') === $formattedValue;
                                    } catch (\Exception $e) {
                                        $isDate = false;
                                    }
                                }
                            @endphp

                            @if ($tipe === 'file')
                                <i> Sudah Diupload </i>
                            @elseif ($isDate)
                                {{ $parsedDate->translatedFormat('d F Y') }}
                            @else
                                {{ $formattedValue }}
                            @endif
                        </td>


                    </tr>
                @endforeach
            </table>
        @endif

        <!-- Penutup -->
        <div class="penutup">
            Demikian Surat Keterangan ini diberikan untuk dapat dipergunakan
            sebagaimana mestinya.
        </div>
    </div>

    <!-- Tanda Tangan -->
    <div class="signature-section">
        <div class="signature-date">
            Balongpanggang,
            {{ \Carbon\Carbon::now()->locale('id')->translatedFormat('d F Y') }}
        </div>
        <div class="signature-title">Kepala Desa,</div>

        @if ($surat->status === 'Diterima' && $user && $user->signature)
            <div style="margin: 20px 0;">
                <img src="{{ public_path($user->signature) }}" alt="Tanda Tangan" style="height: 80px;">
            </div>
        @else
            <div style="height: 80px;"></div>
        @endif

        <div class="signature-name">
            {{ $user->name ?? '.......................................' }}
        </div>
    </div>


    <!-- Lampiran File (opsional) -->
    {{-- @if ($lampiranUrl)
        <div style="margin-top: 40px;">
            <strong>Lampiran:</strong>
            <br>
            <a href="{{ $lampiranUrl }}">{{ $lampiranUrl }}</a>
        </div>
    @endif --}}

</body>

</html>
