USE db_ceklist;
ALTER TABLE ceklist_detail ADD COLUMN nama_petugas VARCHAR(120) NOT NULL DEFAULT '' AFTER foto_bukti;
ALTER TABLE ceklist_harian ADD COLUMN nama_verifikator_pj VARCHAR(120) NULL AFTER waktu_verif_pj;
ALTER TABLE ceklist_harian ADD COLUMN nama_verifikator_kasubbag VARCHAR(120) NULL AFTER waktu_verif_kasubbag;
-- Reset the four demo users to a known-good bcrypt hash for password 123456.
UPDATE users SET password='$2y$12$wWU99p/D1uDq1GEdoNfsQ.gtbSA97oX8O.tm1vg5U9oBoEQXqT7RS' WHERE username IN ('petugas','sarpras','kasubbag','admin');
