-- E-Ceklist BNNK Batang - database fresh-install lengkap
-- File ini mencakup struktur, akun demo, seluruh ruangan/indikator workbook, dan jadwal jam pemeriksaan.
-- Import hanya untuk instalasi baru/database kosong. Script DROP tabel di bawah bersifat menghapus data lama.

CREATE DATABASE IF NOT EXISTS db_ceklist CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_ceklist;

SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS ceklist_detail;
DROP TABLE IF EXISTS ceklist_harian;
DROP TABLE IF EXISTS indikator_ruangan;
DROP TABLE IF EXISTS ruangan;
DROP TABLE IF EXISTS users;
SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nama_lengkap VARCHAR(120) NOT NULL,
 username VARCHAR(50) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL,
 role ENUM('petugas','pj_sarpras','kasubbag_umum','admin') NOT NULL
) ENGINE=InnoDB;

CREATE TABLE ruangan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 kode_qr VARCHAR(60) NOT NULL UNIQUE,
 nama_ruangan VARCHAR(150) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE indikator_ruangan (
 id INT AUTO_INCREMENT PRIMARY KEY,
 ruangan_id INT NOT NULL,
 nama_indikator VARCHAR(180) NOT NULL,
 urutan INT NOT NULL DEFAULT 1,
 is_active TINYINT(1) NOT NULL DEFAULT 1,
 CONSTRAINT fk_indikator_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ceklist_harian (
 id INT AUTO_INCREMENT PRIMARY KEY,
 ruangan_id INT NOT NULL,
 tanggal DATE NOT NULL,
 no_polisi VARCHAR(40) NULL,
 catatan_temuan TEXT NULL,
 verif_pj_sarpras TINYINT(1) NOT NULL DEFAULT 0,
 waktu_verif_pj DATETIME NULL,
 nama_verifikator_pj VARCHAR(120) NULL,
 verif_kasubbag TINYINT(1) NOT NULL DEFAULT 0,
 waktu_verif_kasubbag DATETIME NULL,
 nama_verifikator_kasubbag VARCHAR(120) NULL,
 UNIQUE KEY unik_ruangan_tanggal (ruangan_id,tanggal),
 CONSTRAINT fk_harian_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ceklist_detail (
 id INT AUTO_INCREMENT PRIMARY KEY,
 ceklist_harian_id INT NOT NULL,
 jam_cek VARCHAR(5) NOT NULL,
 indikator_id INT NOT NULL,
 status ENUM('Bersih/Rapi','Kotor/Perlu Tindakan') NOT NULL,
 foto_bukti VARCHAR(255) NULL,
 nama_petugas VARCHAR(120) NOT NULL,
 petugas_id INT NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unik_detail_shift_indikator (ceklist_harian_id,jam_cek,indikator_id),
 CONSTRAINT fk_detail_harian FOREIGN KEY (ceklist_harian_id) REFERENCES ceklist_harian(id) ON DELETE CASCADE,
 CONSTRAINT fk_detail_indikator FOREIGN KEY (indikator_id) REFERENCES indikator_ruangan(id) ON DELETE CASCADE,
 CONSTRAINT fk_detail_petugas FOREIGN KEY (petugas_id) REFERENCES users(id)
) ENGINE=InnoDB;

INSERT INTO users (nama_lengkap,username,password,role) VALUES
('Dewi Rizqiani, A.Md','petugas','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','petugas'),
('Tri Ibnoegraha, SIP','pj_sarpras','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','pj_sarpras'),
('Nurul Dwi Rahayuningsih, S.I.Kom','kasubbag','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','kasubbag_umum'),
('Administrator','admin','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','admin');

-- Seed ruangan, indikator, dan jadwal sesuai workbook Excel.
USE db_ceklist;
-- Jadwal pemeriksaan per ruangan.
CREATE TABLE jadwal_jam_ruangan (ruangan_id INT NOT NULL, jam_cek VARCHAR(5) NOT NULL, urutan INT NOT NULL, PRIMARY KEY (ruangan_id,jam_cek), CONSTRAINT fk_jadwal_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE) ENGINE=InnoDB;

-- Tambah/rename room by QR code; kode QR lama tetap dipertahankan untuk ruangan lama.
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('LOBBY-01','Lobby BNNK Batang') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('PANTRY-01','Pantry BNNK Batang') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('KERJA-01','Ruang Kerja Lt. 1 (Pemberantasan dan Rehabilitasi)') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('RAPAT-01','Ruang Rapat') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('TAMU-01','Ruang Tengah dan Selasar Lt. I') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('TOILET-02','Toilet Lt. 2 Kantor BNNK Batang') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('KERJA2-01','Ruang Kerja Lt. 2 (Ruang Kepala, P2M dan Subbag Umum)') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('KLINIK-01','Ruang Klinik dan Konseling') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('MUSHOLA-01','Mushola dan Selasar Lt. 2') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('HALBEL-01','Halaman Belakang') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('HALDEP-01','Halaman Depan') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('SATPAM-01','Ruang Pos Satpam') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);
INSERT INTO ruangan(kode_qr,nama_ruangan) VALUES ('KENDARAAN-01','Kendaraan Dinas') ON DUPLICATE KEY UPDATE nama_ruangan=VALUES(nama_ruangan);

CREATE TEMPORARY TABLE tmp_indikator_excel (kode_qr VARCHAR(60), urutan INT, nama_indikator VARCHAR(180));

INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',1,'Lantai bersih, langit-langit bebas sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',2,'Kursi respsion/tamu rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',3,'Meja resepsionis rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',4,'Tempat sampah kosong');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',5,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',6,'Peralatan dan perlengkapan kantor yang ada di lobby bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',7,'Tersedia minuman untuk tamu');
INSERT INTO tmp_indikator_excel VALUES ('LOBBY-01',8,'Area kaca/pintu dan jendela bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',1,'Lantai bersih dan kering');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',2,'Meja dan almari rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',3,'Wastafel rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',5,'Peralatan pantry rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',6,'Dapur/kompor bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',7,'Area kaca jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',8,'Kulkas bersih');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',9,'Air galon dan termos tersedia');
INSERT INTO tmp_indikator_excel VALUES ('PANTRY-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',1,'Lantai bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',2,'Meja kerja rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',3,'Kursi tertata dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',5,'Peralatan kantor tertata rapi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',6,'AC/Kipas berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',7,'Lampu berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',8,'Jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',9,'Dokumen tersimpan rapi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KERJA-01',11,'Area kerja bebas bau/tumpahan');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',1,'Lantai bersih');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',2,'Meja kerja rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',3,'Kursi tertata dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',5,'Peralatan kantor tertata rapi');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',6,'AC/Kipas berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',7,'Lampu berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',8,'Jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',9,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('RAPAT-01',10,'Area kerja bebas bau/tumpahan');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',1,'Kondisi lantai');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',2,'Meja bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',3,'Kursi bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',5,'Kerapihan ruangan');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',6,'Area kaca jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',7,'Pelengkapan pojok edukasi rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',8,'Gudang/Janitor rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',9,'Ruang Laktasi bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('TAMU-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',1,'Lantai toilet bersih dan kering');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',2,'Kloset bersih');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',3,'Wastafel bersih');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',4,'Cermin bersih');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',5,'Tempat sampah dikosongkan');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',6,'Sabun cuci tangan tersedia');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',7,'Tisu toilet dan towel tersedia');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',8,'Tidak ada bau menyengat');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',9,'Saluran air lancar');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',10,'Pintu dan dinding bersih');
INSERT INTO tmp_indikator_excel VALUES ('TOILET-02',11,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',1,'Lantai bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',2,'Meja kerja rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',3,'Kursi tertata dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',5,'Peralatan kantor tertata rapi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',6,'AC/Kipas berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',7,'Lampu berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',8,'Jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',9,'Dokumen tersimpan rapi');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KERJA2-01',11,'Area kerja bebas bau/tumpahan');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',1,'Lantai bersih');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',2,'Meja kerja rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',3,'Kursi tertata dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',5,'Peralatan kantor tertata rapi');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',6,'AC/Kipas berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',7,'Lampu berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',8,'Jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',9,'Kebersihan bed periksa dan tangga');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KLINIK-01',11,'Area kerja bebas bau/tumpahan');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',1,'Lantai dan karpet bersih');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',2,'Peralatan ibadah rapi dan bersih');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',3,'Kebersihan tempat wudhu');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',5,'AC/Kipas berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',6,'Lampu berfungsi');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',7,'Jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',8,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('MUSHOLA-01',9,'Area mushola dan selasar bebas bau/tumpahan');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',1,'Kondisi lantai');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',2,'Meja bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',3,'Kursi bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',5,'Kerapihan halaman');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',6,'Area kaca jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',7,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',8,'Mushola dan tempat wudhu bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('HALBEL-01',9,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',1,'Kondisi lantai teras dan halaman/taman bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',2,'Tanaman bersih dan terawat');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',3,'Wastafel bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',4,'Sampah telah dibuang');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',5,'Kerapihan halaman');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',6,'Area kaca jendela dan pintu bersih');
INSERT INTO tmp_indikator_excel VALUES ('HALDEP-01',7,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',1,'Lantai toilet bersih dan kering');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',2,'Kloset bersih');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',3,'Wastafel bersih');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',4,'Tempat sampah dikosongkan');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',5,'Tidak ada bau menyengat');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',6,'Saluran air lancar');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',7,'Pintu, dinding, jendela dan kaca bersih');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',8,'Lantai ruangan pos satpam bersih');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',9,'Meja dan kursi bersih dan rapi');
INSERT INTO tmp_indikator_excel VALUES ('SATPAM-01',10,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',1,'Eksterior kendaraan bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',2,'Kaca depan/samping bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',3,'Spion bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',4,'Interior bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',5,'Dashboard bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',6,'Karpet bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',7,'Jok bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',8,'Bagasi bersih');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',9,'Tempat sampah dikosongkan');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',10,'Peralatan darurat tersedia');
INSERT INTO tmp_indikator_excel VALUES ('KENDARAAN-01',11,'Tidak ada bau tidak sedap');

-- Nonaktifkan sementara semua indikator room yang diselaraskan; record lama tidak dihapus.
UPDATE indikator_ruangan i JOIN ruangan r ON r.id=i.ruangan_id SET i.is_active=0 WHERE r.kode_qr IN ('LOBBY-01','PANTRY-01','KERJA-01','RAPAT-01','TAMU-01','TOILET-02','KERJA2-01','KLINIK-01','MUSHOLA-01','HALBEL-01','HALDEP-01','SATPAM-01','KENDARAAN-01');
-- Update indikator yang sudah ada berdasarkan urutan agar detail lama tetap terhubung.
UPDATE indikator_ruangan i JOIN ruangan r ON r.id=i.ruangan_id JOIN tmp_indikator_excel x ON x.kode_qr=r.kode_qr AND x.urutan=i.urutan SET i.nama_indikator=x.nama_indikator, i.is_active=1;
-- Tambahkan indikator baru jika belum ada pasangan ruangan/urutan.
INSERT INTO indikator_ruangan (ruangan_id,nama_indikator,urutan,is_active) SELECT r.id,x.nama_indikator,x.urutan,1 FROM tmp_indikator_excel x JOIN ruangan r ON r.kode_qr=x.kode_qr LEFT JOIN indikator_ruangan i ON i.ruangan_id=r.id AND i.urutan=x.urutan WHERE i.id IS NULL;

-- Atur jadwal cek tiap ruangan sesuai Excel.
DELETE j FROM jadwal_jam_ruangan j JOIN ruangan r ON r.id=j.ruangan_id WHERE r.kode_qr IN ('LOBBY-01','PANTRY-01','KERJA-01','RAPAT-01','TAMU-01','TOILET-02','KERJA2-01','KLINIK-01','MUSHOLA-01','HALBEL-01','HALDEP-01','SATPAM-01','KENDARAAN-01');
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='LOBBY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='LOBBY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='LOBBY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='PANTRY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='PANTRY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='PANTRY-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='KERJA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='KERJA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='KERJA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='RAPAT-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='RAPAT-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='RAPAT-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='TAMU-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='TAMU-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='TAMU-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='TOILET-02';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='TOILET-02';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',3 FROM ruangan WHERE kode_qr='TOILET-02';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='KERJA2-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='KERJA2-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='KERJA2-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='KLINIK-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='KLINIK-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',3 FROM ruangan WHERE kode_qr='KLINIK-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='MUSHOLA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='MUSHOLA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',3 FROM ruangan WHERE kode_qr='MUSHOLA-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='HALBEL-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='HALBEL-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',3 FROM ruangan WHERE kode_qr='HALBEL-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='HALDEP-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='HALDEP-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',3 FROM ruangan WHERE kode_qr='HALDEP-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='SATPAM-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'10:00',2 FROM ruangan WHERE kode_qr='SATPAM-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='SATPAM-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'14:00',4 FROM ruangan WHERE kode_qr='SATPAM-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'08:00',1 FROM ruangan WHERE kode_qr='KENDARAAN-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'11:00',2 FROM ruangan WHERE kode_qr='KENDARAAN-01';
INSERT INTO jadwal_jam_ruangan (ruangan_id,jam_cek,urutan) SELECT id,'12:00',3 FROM ruangan WHERE kode_qr='KENDARAAN-01';
DROP TEMPORARY TABLE tmp_indikator_excel;
