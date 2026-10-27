USE praktikum_web_2401020048;

INSERT INTO program_studi (nama_prodi) VALUES
    ('Teknik Mesin'),
    ('Sistem Elektro');

INSERT INTO mahasiswa
    (nim, nama, email, usia, program_studi_id)
VALUES
    ('2401020048', 'Azharrodhi Gustiarya',
     'azharrodhi@example.com', 20, 1),
    ('2401020002', 'Madi Pratama',
     'madipratama@example.com', 19, 1),
    ('2401020001', 'Jimi Siregar',
     'jimisiregar@example.com', 21, 2),
    ('2401020099', 'Kiting',
     'kiting@example.com', 18, 2);

UPDATE mahasiswa
SET email = 'azharrodhigustiarya@example.com'
WHERE nim = '2401020048';

DELETE FROM mahasiswa
WHERE nim = '2401020099';

SELECT m.nim, m.nama, m.email, m.usia,
       p.nama_prodi
FROM mahasiswa AS m
JOIN program_studi AS p
    ON p.id = m.program_studi_id
ORDER BY m.nim;
