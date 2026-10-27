<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Peserta Pelatihan</title>
</head>
<body>
    <h1>Data Peserta Pelatihan</h1>

    @if (session('berhasil'))
        <p>{{ session('berhasil') }}</p>
    @endif
    @if (session('gagal'))
        <p>{{ session('gagal') }}</p>
    @endif

    @if ($errors->any())
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    @endif

    <h2>Tambah Peserta Pelatihan</h2>
    <form action="{{ route('peserta.store') }}" method="POST">
        @csrf
        <p>Kode Peserta<br>
            <input type="text" name="kode_peserta" value="{{ old('kode_peserta') }}" maxlength="10">
        </p>
        <p>Nama Peserta<br>
            <input type="text" name="nama_peserta" value="{{ old('nama_peserta') }}">
        </p>
        <p>Email<br>
            <input type="email" name="email" value="{{ old('email') }}">
        </p>
        <p>Usia<br>
            <input type="number" name="usia" value="{{ old('usia') }}">
        </p>
        <p>Program Pelatihan<br>
            <select name="program_pelatihan_id">
                <option value="">-- Pilih Program Pelatihan --</option>
                @foreach ($daftarProgramPelatihan as $programPelatihan)
                    <option value="{{ $programPelatihan['id'] }}"
                        @selected(old('program_pelatihan_id') == $programPelatihan['id'])>
                        {{ $programPelatihan['nama_program'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Tambah Data</button>
    </form>

    <h2>Pencarian dan Filter</h2>
    <form action="{{ route('peserta.index') }}" method="GET">
        <p>Kata Kunci<br>
            <input type="text" name="q" value="{{ $kataKunci }}">
        </p>
        <p>Program Pelatihan<br>
            <select name="program_pelatihan_id">
                <option value="">-- Semua Program Pelatihan --</option>
                @foreach ($daftarProgramPelatihan as $programPelatihan)
                    <option value="{{ $programPelatihan['id'] }}"
                        @selected((string) $programPelatihanId === (string) $programPelatihan['id'])>
                        {{ $programPelatihan['nama_program'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Cari</button>
        <a href="{{ route('peserta.index') }}">Reset</a>
    </form>

    <h2>Daftar Peserta Pelatihan</h2>
    <table border="1" cellpadding="6" cellspacing="0">
        <tr>
            <th>Kode Peserta</th>
            <th>Nama Peserta</th>
            <th>Email</th>
            <th>Usia</th>
            <th>Status Usia</th>
            <th>Program Pelatihan</th>
            <th>Aksi</th>
        </tr>
        @forelse ($daftarPeserta as $peserta)
            <tr>
                <td>{{ $peserta['kode_peserta'] }}</td>
                <td>{{ $peserta['nama_peserta'] }}</td>
                <td>{{ $peserta['email'] }}</td>
                <td>{{ $peserta['usia'] }}</td>
                <td>{{ statusUsia((int) $peserta['usia']) }}</td>
                <td>{{ $peserta['nama_program'] }}</td>
                <td>
                    <a href="{{ route('peserta.edit', $peserta['id']) }}">
                        Ubah
                    </a>
                    <form action="{{ route('peserta.destroy', $peserta['id']) }}"
                          method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            onclick="return confirm('Hapus data ini?')">
                            Hapus
                        </button>
                    </form>
                </td>
            </tr>
        @empty
            <tr><td colspan="7">Data tidak ditemukan.</td></tr>
        @endforelse
    </table>
</body>
</html>
