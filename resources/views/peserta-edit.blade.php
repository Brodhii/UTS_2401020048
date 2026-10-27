<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Peserta Pelatihan</title>
</head>
<body>
    <h1>Ubah Peserta Pelatihan</h1>

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

    <form action="{{ route('peserta.update', $peserta['id']) }}"
          method="POST">
        @csrf
        @method('PUT')
        <p>Kode Peserta<br>
            <input type="text" name="kode_peserta"
                   value="{{ old('kode_peserta', $peserta['kode_peserta']) }}" maxlength="10">
        </p>
        <p>Nama Peserta<br>
            <input type="text" name="nama_peserta"
                   value="{{ old('nama_peserta', $peserta['nama_peserta']) }}">
        </p>
        <p>Email<br>
            <input type="email" name="email"
                   value="{{ old('email', $peserta['email']) }}">
        </p>
        <p>Usia<br>
            <input type="number" name="usia"
                   value="{{ old('usia', $peserta['usia']) }}">
        </p>
        <p>Program Pelatihan<br>
            <select name="program_pelatihan_id">
                @foreach ($daftarProgramPelatihan as $programPelatihan)
                    <option value="{{ $programPelatihan['id'] }}"
                        @selected(old(
                            'program_pelatihan_id',
                            $peserta['program_pelatihan_id']
                        ) == $programPelatihan['id'])>
                        {{ $programPelatihan['nama_program'] }}
                    </option>
                @endforeach
            </select>
        </p>
        <button type="submit">Simpan Perubahan</button>
        <a href="{{ route('peserta.index') }}">Kembali</a>
    </form>
</body>
</html>
