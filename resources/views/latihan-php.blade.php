<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Latihan PHP - Modul 2</title>
</head>
<body>
    <h1>Hasil Latihan PHP (Modul 2)</h1>
    <p>Nama: {{ $nama }}</p>

    <h2>Pengujian Fungsi statusUsia()</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>No</th>
            <th>Usia</th>
            <th>Status Usia (Fungsi statusUsia())</th>
            <th>Keterangan (Percabangan Blade)</th>
        </tr>
        @foreach ($daftarUsia as $index => $usia)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $usia }} tahun</td>
                <td>{{ statusUsia($usia) }}</td>
                <td>
                    @if ($usia >= 21)
                        Dewasa (karena usia &ge; 21)
                    @else
                        Remaja Akhir (karena usia &lt; 21)
                    @endif
                </td>
            </tr>
        @endforeach
    </table>

    <p><a href="{{ route('peserta.index') }}">Menuju Halaman Utama Peserta</a></p>
</body>
</html>
