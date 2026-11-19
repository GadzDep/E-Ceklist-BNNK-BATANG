<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['pj_sarpras','kasubbag_umum']);
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('verifikator/dashboard.php');verify_csrf();
$id=(int)($_POST['id']??0);$jenis=$_POST['jenis']??'';$date=$_POST['tanggal']??date('Y-m-d');
$nama=trim($_POST['nama_verifikator']??'');
if($nama===''||mb_strlen($nama)>120)exit('Nama verifikator wajib diisi (maksimal 120 karakter).');
if($id<1)redirect('verifikator/dashboard.php');
if($jenis==='pj'&&current_role()==='pj_sarpras')$sql='UPDATE ceklist_harian SET verif_pj_sarpras=1,waktu_verif_pj=NOW(),nama_verifikator_pj=? WHERE id=? AND verif_pj_sarpras=0';
elseif($jenis==='kasubbag'&&current_role()==='kasubbag_umum')$sql='UPDATE ceklist_harian SET verif_kasubbag=1,waktu_verif_kasubbag=NOW(),nama_verifikator_kasubbag=? WHERE id=? AND verif_kasubbag=0';
else{http_response_code(403);exit('Tidak berwenang melakukan verifikasi ini.');}
$s=$conn->prepare($sql);$s->bind_param('si',$nama,$id);$s->execute();
$s=$conn->prepare('SELECT ruangan_id FROM ceklist_harian WHERE id=?');$s->bind_param('i',$id);$s->execute();$r=$s->get_result()->fetch_assoc();
redirect('verifikator/detail_ceklist.php?ruangan='.(int)($r['ruangan_id']??0).'&tanggal='.urlencode($date));
