<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['pj_sarpras','kasubbag_umum']);
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('verifikator/dashboard.php');verify_csrf();
$id=(int)($_POST['id']??0);$jenis=$_POST['jenis']??'';$date=$_POST['tanggal']??date('Y-m-d');
$userId=(int)($_SESSION['user']['id']??0);$role=current_role();
$s=$conn->prepare('SELECT nama_lengkap FROM users WHERE id=? AND role=? LIMIT 1');$s->bind_param('is',$userId,$role);$s->execute();$nama=trim((string)($s->get_result()->fetch_assoc()['nama_lengkap']??''));if($nama===''||mb_strlen($nama)>120)exit('Nama akun verifikator tidak valid. Silakan hubungi admin.');
if($id<1)redirect('verifikator/dashboard.php');
$note=trim((string)($_POST['catatan_verifikator']??''));if(mb_strlen($note)>5000)exit('Catatan verifikator maksimal 5000 karakter.');
if($jenis==='pj'&&$role==='pj_sarpras'){$sql='UPDATE ceklist_harian SET catatan_pj_sarpras=?,verif_pj_sarpras=1,waktu_verif_pj=NOW(),nama_verifikator_pj=? WHERE id=? AND verif_pj_sarpras=0';}
elseif($jenis==='kasubbag'&&$role==='kasubbag_umum'){$sql='UPDATE ceklist_harian SET catatan_kasubbag_umum=?,verif_kasubbag=1,waktu_verif_kasubbag=NOW(),nama_verifikator_kasubbag=? WHERE id=? AND verif_kasubbag=0';}
else{http_response_code(403);exit('Tidak berwenang melakukan verifikasi ini.');}
$s=$conn->prepare($sql);$s->bind_param('ssi',$note,$nama,$id);$s->execute();
$s=$conn->prepare('SELECT ruangan_id FROM ceklist_harian WHERE id=?');$s->bind_param('i',$id);$s->execute();$r=$s->get_result()->fetch_assoc();
redirect('verifikator/detail_ceklist.php?ruangan='.(int)($r['ruangan_id']??0).'&tanggal='.urlencode($date).'&verifikasi=success&jenis='.urlencode($jenis));
