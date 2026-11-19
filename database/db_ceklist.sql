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
 CONSTRAINT fk_indikator_ruangan FOREIGN KEY (ruangan_id) REFERENCES ruangan(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ceklist_harian (
 id INT AUTO_INCREMENT PRIMARY KEY,
 ruangan_id INT NOT NULL,
 tanggal DATE NOT NULL,
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
 jam_cek ENUM('08:00','11:00','14:00') NOT NULL,
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
('Petugas Kebersihan','petugas','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','petugas'),
('PJ Sarpras','pj_sarpras','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','pj_sarpras'),
('Kasubbag Umum','kasubbag','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','kasubbag_umum'),
('Administrator','admin','$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS','admin');

INSERT INTO ruangan (kode_qr,nama_ruangan) VALUES
('LOBBY-01','Ruang Lobby BNNK Batang'),
('PANTRY-01','Pantry BNNK Batang'),
('KERJA-01','Ruang Kerja BNNK Batang'),
('RAPAT-01','Ruang Rapat BNNK Batang'),
('TAMU-01','Ruang Tamu Tengah BNNK Batang'),
('TOILET-02','Toilet Lt. 2 Kantor BNNK Batang');

INSERT INTO indikator_ruangan (ruangan_id,nama_indikator,urutan) VALUES
(1,'Lantai Bersih',1),(1,'Kursi/Tamu Rapi',2),(1,'Meja Resepsionis Bersih',3),(1,'Tempat Sampah Kosong',4),(1,'Area Kaca/Pintu Bersih',5),
(2,'Kebersihan Lantai',1),(2,'Kebersihan Meja',2),(2,'Kebersihan Wastafel',3),(2,'Tempat Sampah',4),(2,'Peralatan Pantry',5),
(3,'Lantai bersih',1),(3,'Meja kerja rapi',2),(3,'Kursi tertata',3),(3,'Sampah telah dibuang',4),(3,'Peralatan kantor tertata',5),(3,'AC & Kipas Berfungsi',6),(3,'Lampu berfungsi',7),(3,'Jendela dan pintu bersih',8),(3,'Dokumen tersimpan rapi',9),(3,'Area kerja bebas bau/tumpahan',10),
(4,'Kebersihan Lantai',1),(4,'Kursi & Meja Rapi',2),(4,'AC & Kipas Berfungsi',3),(4,'Peralatan Presentasi',4),
(5,'Kondisi Lantai',1),(5,'Kebersihan Meja',2),(5,'Kebersihan Kursi',3),(5,'Tempat Sampah',4),(5,'Kerapian Ruangan',5),
(6,'Lantai toilet bersih dan kering',1),(6,'Kloset bersih',2),(6,'Wastafel bersih',3),(6,'Cermin bersih',4),(6,'Tempat sampah dikosongkan',5),(6,'Sabun cuci tangan tersedia',6),(6,'Tisu toilet dan towel tersedia',7),(6,'Tidak ada bau menyengat',8),(6,'Saluran air lancar',9),(6,'Pintu dan dinding bersih',10),(6,'Kondisi ruangan dan langit-langit bebas debu dan sarang laba-laba',11);
