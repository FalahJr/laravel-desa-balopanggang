<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Detail Surat Masuk</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 20px;
        }

        h2 {
            margin-bottom: 10px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        td,
        th {
            padding: 8px 12px;
            border: 1px solid #ddd;
        }

        th {
            background: #f2f2f2;
            text-align: left;
        }
    </style>
</head>

<body>
    <h2>Detail Surat Masuk</h2>

    <table>
        <tr>
            <th>Nomor Surat</th>
            <td>{{ $surat->nomor_surat }}</td>
        </tr>
        <tr>
            <th>Tanggal Surat</th>
            <td>{{ $surat->tanggal_surat }}</td>
        </tr>
        <tr>
            <th>Nama Surat</th>
            <td>{{ $surat->nama_surat }}</td>
        </tr>
        <tr>
            <th>Jenis Surat</th>
            <td>{{ $surat->jenisSurat->nama }}</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $surat->status }}</td>
        </tr>
        <tr>
            <th>Lampiran</th>
            <td>
                @if ($lampiranUrl)
                    <a href="{{ $lampiranUrl }}" target="_blank">{{ $lampiranUrl }}</a>
                @else
                    Tidak ada
                @endif
            </td>
        </tr>
    </table>

    @if ($fields->count())
        <h3 style="margin-top: 30px;">Data Tambahan</h3>
        <table>
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Nilai</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($fields as $field)
                    <tr>
                        <td>{{ $field['label'] }}</td>
                        <td>{{ $field['value'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>

</html>
