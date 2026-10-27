<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

if (! function_exists('statusUsia')) {
    /**
     * Menentukan kategori status usia peserta pelatihan.
     * Mengembalikan 'Dewasa' jika usia >= 21, dan 'Remaja Akhir' jika di bawah 21.
     */
    function statusUsia(int $usia): string
    {
        if ($usia >= 21) {
            return 'Dewasa';
        }

        return 'Remaja Akhir';
    }
}

Route::get('/', function () {
    return redirect()->route('peserta.index');
});

Route::get('/latihan-php', function () {
    $nama = 'Azharrodhi Gustiarya';
    $daftarUsia = [18, 20, 21, 24, 17, 30];

    return view('latihan-php', compact('nama', 'daftarUsia'));
})->name('latihan.php');

Route::get('/peserta', function (Request $request) {
    $kataKunci = trim((string) $request->query('q', ''));
    $programPelatihanId = $request->query('program_pelatihan_id', '');

    try {
        $pdo = DB::connection()->getPdo();

        $sql = 'SELECT p.id, p.kode_peserta, p.nama_peserta, p.email, p.usia,
                       p.program_pelatihan_id, pr.nama_program
                FROM peserta AS p
                JOIN program_pelatihan AS pr
                  ON pr.id = p.program_pelatihan_id
                WHERE 1 = 1';
        $parameter = [];

        if ($kataKunci !== '') {
            $sql .= ' AND (p.kode_peserta LIKE :kata_kunci_kode
                      OR p.nama_peserta LIKE :kata_kunci_nama
                      OR p.email LIKE :kata_kunci_email)';
            $nilaiPencarian = '%'.$kataKunci.'%';
            $parameter['kata_kunci_kode'] = $nilaiPencarian;
            $parameter['kata_kunci_nama'] = $nilaiPencarian;
            $parameter['kata_kunci_email'] = $nilaiPencarian;
        }

        if ($programPelatihanId !== null && $programPelatihanId !== '') {
            $sql .= ' AND p.program_pelatihan_id = :program_pelatihan_id';
            $parameter['program_pelatihan_id'] = (int) $programPelatihanId;
        }

        $sql .= ' ORDER BY p.kode_peserta';
        $ambilPeserta = $pdo->prepare($sql);
        $ambilPeserta->execute($parameter);
        $daftarPeserta = $ambilPeserta->fetchAll(PDO::FETCH_ASSOC);

        $ambilProgram = $pdo->prepare(
            'SELECT id, nama_program
             FROM program_pelatihan
             ORDER BY nama_program'
        );
        $ambilProgram->execute();
        $daftarProgramPelatihan = $ambilProgram->fetchAll(PDO::FETCH_ASSOC);

        return view('peserta', compact(
            'daftarPeserta',
            'daftarProgramPelatihan',
            'kataKunci',
            'programPelatihanId'
        ));
    } catch (Throwable $error) {
        report($error);

        return response(
            'Data tidak dapat dibaca. Periksa koneksi dan query.',
            500
        );
    }
})->name('peserta.index');

Route::post('/peserta', function (Request $request) {
    $request->merge([
        'kode_peserta' => strtoupper(trim(strip_tags((string) $request->input('kode_peserta')))),
        'nama_peserta' => trim(strip_tags((string) $request->input('nama_peserta'))),
        'email' => trim(strip_tags((string) $request->input('email'))),
        'usia' => trim((string) $request->input('usia')),
        'program_pelatihan_id' => $request->input('program_pelatihan_id'),
    ]);

    $data = $request->validate([
        'kode_peserta' => ['required', 'alpha_num', 'size:10'],
        'nama_peserta' => ['required', 'string', 'min:3', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:17', 'max:60'],
        'program_pelatihan_id' => ['required', 'integer'],
    ], [
        'kode_peserta.required' => 'Kode peserta wajib diisi.',
        'kode_peserta.alpha_num' => 'Kode peserta harus alfanumerik.',
        'kode_peserta.size' => 'Kode peserta harus tepat 10 karakter.',
        'nama_peserta.required' => 'Nama peserta wajib diisi.',
        'nama_peserta.min' => 'Nama peserta minimal 3 karakter.',
        'nama_peserta.max' => 'Nama peserta maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus bilangan bulat.',
        'usia.min' => 'Usia minimal 17 tahun.',
        'usia.max' => 'Usia maksimal 60 tahun.',
        'program_pelatihan_id.required' => 'Program pelatihan wajib dipilih.',
        'program_pelatihan_id.integer' => 'Program pelatihan tidak valid.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cekProgram = $pdo->prepare(
            'SELECT COUNT(*) FROM program_pelatihan WHERE id = :id'
        );
        $cekProgram->execute(['id' => $data['program_pelatihan_id']]);
        if ((int) $cekProgram->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Program pelatihan tidak valid atau tidak tersedia.'
            );
        }

        $cekKode = $pdo->prepare(
            'SELECT COUNT(*) FROM peserta WHERE kode_peserta = :kode_peserta'
        );
        $cekKode->execute(['kode_peserta' => $data['kode_peserta']]);

        if ((int) $cekKode->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode peserta sudah digunakan.'
            );
        }

        $cekEmail = $pdo->prepare(
            'SELECT COUNT(*) FROM peserta WHERE email = :email'
        );
        $cekEmail->execute(['email' => $data['email']]);

        if ((int) $cekEmail->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Email sudah digunakan.'
            );
        }

        $simpan = $pdo->prepare(
            'INSERT INTO peserta
             (kode_peserta, nama_peserta, email, usia, program_pelatihan_id)
             VALUES (:kode_peserta, :nama_peserta, :email, :usia, :program_pelatihan_id)'
        );
        $simpan->execute([
            'kode_peserta' => $data['kode_peserta'],
            'nama_peserta' => $data['nama_peserta'],
            'email' => $data['email'],
            'usia' => (int) $data['usia'],
            'program_pelatihan_id' => (int) $data['program_pelatihan_id'],
        ]);

        return redirect()->route('peserta.index')->with(
            'berhasil',
            'Data peserta berhasil ditambahkan.'
        );
    } catch (Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal disimpan. Periksa koneksi dan data.'
        );
    }
})->name('peserta.store');

Route::get('/peserta/{id}/edit', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();
        $ambilPeserta = $pdo->prepare(
            'SELECT id, kode_peserta, nama_peserta, email, usia, program_pelatihan_id
             FROM peserta WHERE id = :id'
        );
        $ambilPeserta->execute(['id' => $id]);
        $peserta = $ambilPeserta->fetch(PDO::FETCH_ASSOC);

        if (! $peserta) {
            return redirect()->route('peserta.index')->with(
                'gagal',
                'Data peserta tidak ditemukan.'
            );
        }

        $ambilProgram = $pdo->prepare(
            'SELECT id, nama_program FROM program_pelatihan ORDER BY nama_program'
        );
        $ambilProgram->execute();
        $daftarProgramPelatihan = $ambilProgram->fetchAll(PDO::FETCH_ASSOC);

        return view('peserta-edit', compact(
            'peserta',
            'daftarProgramPelatihan'
        ));
    } catch (Throwable $error) {
        report($error);

        return redirect()->route('peserta.index')->with(
            'gagal',
            'Data gagal dibuka untuk diubah.'
        );
    }
})->name('peserta.edit');

Route::put('/peserta/{id}', function (Request $request, int $id) {
    $request->merge([
        'kode_peserta' => strtoupper(trim(strip_tags((string) $request->input('kode_peserta')))),
        'nama_peserta' => trim(strip_tags((string) $request->input('nama_peserta'))),
        'email' => trim(strip_tags((string) $request->input('email'))),
        'usia' => trim((string) $request->input('usia')),
        'program_pelatihan_id' => $request->input('program_pelatihan_id'),
    ]);

    $data = $request->validate([
        'kode_peserta' => ['required', 'alpha_num', 'size:10'],
        'nama_peserta' => ['required', 'string', 'min:3', 'max:100'],
        'email' => ['required', 'email', 'max:100'],
        'usia' => ['required', 'integer', 'min:17', 'max:60'],
        'program_pelatihan_id' => ['required', 'integer'],
    ], [
        'kode_peserta.required' => 'Kode peserta wajib diisi.',
        'kode_peserta.alpha_num' => 'Kode peserta harus alfanumerik.',
        'kode_peserta.size' => 'Kode peserta harus tepat 10 karakter.',
        'nama_peserta.required' => 'Nama peserta wajib diisi.',
        'nama_peserta.min' => 'Nama peserta minimal 3 karakter.',
        'nama_peserta.max' => 'Nama peserta maksimal 100 karakter.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.max' => 'Email maksimal 100 karakter.',
        'usia.required' => 'Usia wajib diisi.',
        'usia.integer' => 'Usia harus bilangan bulat.',
        'usia.min' => 'Usia minimal 17 tahun.',
        'usia.max' => 'Usia maksimal 60 tahun.',
        'program_pelatihan_id.required' => 'Program pelatihan wajib dipilih.',
        'program_pelatihan_id.integer' => 'Program pelatihan tidak valid.',
    ]);

    try {
        $pdo = DB::connection()->getPdo();

        $cekProgram = $pdo->prepare(
            'SELECT COUNT(*) FROM program_pelatihan WHERE id = :id'
        );
        $cekProgram->execute(['id' => $data['program_pelatihan_id']]);
        if ((int) $cekProgram->fetchColumn() === 0) {
            return back()->withInput()->with(
                'gagal',
                'Program pelatihan tidak valid atau tidak tersedia.'
            );
        }

        $cekKode = $pdo->prepare(
            'SELECT COUNT(*) FROM peserta
             WHERE kode_peserta = :kode_peserta AND id <> :id'
        );
        $cekKode->execute([
            'kode_peserta' => $data['kode_peserta'],
            'id' => $id,
        ]);

        if ((int) $cekKode->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Kode peserta sudah digunakan.'
            );
        }

        $cekEmail = $pdo->prepare(
            'SELECT COUNT(*) FROM peserta
             WHERE email = :email AND id <> :id'
        );
        $cekEmail->execute([
            'email' => $data['email'],
            'id' => $id,
        ]);

        if ((int) $cekEmail->fetchColumn() > 0) {
            return back()->withInput()->with(
                'gagal',
                'Email sudah digunakan.'
            );
        }

        $ubah = $pdo->prepare(
            'UPDATE peserta
             SET kode_peserta = :kode_peserta, nama_peserta = :nama_peserta,
                 email = :email, usia = :usia, program_pelatihan_id = :program_pelatihan_id
             WHERE id = :id'
        );
        $ubah->execute([
            'kode_peserta' => $data['kode_peserta'],
            'nama_peserta' => $data['nama_peserta'],
            'email' => $data['email'],
            'usia' => (int) $data['usia'],
            'program_pelatihan_id' => (int) $data['program_pelatihan_id'],
            'id' => $id,
        ]);

        return redirect()->route('peserta.index')->with(
            'berhasil',
            'Data peserta berhasil diperbarui.'
        );
    } catch (Throwable $error) {
        report($error);

        return back()->withInput()->with(
            'gagal',
            'Data gagal diperbarui. Periksa koneksi dan data.'
        );
    }
})->name('peserta.update');

Route::delete('/peserta/{id}', function (int $id) {
    try {
        $pdo = DB::connection()->getPdo();
        $hapus = $pdo->prepare(
            'DELETE FROM peserta WHERE id = :id'
        );
        $hapus->execute(['id' => $id]);

        if ($hapus->rowCount() === 0) {
            return redirect()->route('peserta.index')->with(
                'gagal',
                'Data peserta tidak ditemukan.'
            );
        }

        return redirect()->route('peserta.index')->with(
            'berhasil',
            'Data peserta berhasil dihapus.'
        );
    } catch (Throwable $error) {
        report($error);

        return redirect()->route('peserta.index')->with(
            'gagal',
            'Data gagal dihapus. Periksa relasi atau koneksi.'
        );
    }
})->name('peserta.destroy');
