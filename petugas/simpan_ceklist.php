<?php
require_once __DIR__.'/../config/koneksi.php'; require_role(['petugas']);
if($_SERVER['REQUEST_METHOD']!=='POST')redirect('petugas/index.php'); verify_csrf();
$rid=(int)($_POST['ruangan_id']??0);$jam=$_POST['jam_cek']??'';$validJam=['08:00','11:00','14:00'];
if($rid<1||!in_array($jam,$validJam,true))redirect('petugas/index.php');
$s=$conn->prepare('SELECT id FROM ruangan WHERE id=?');$s->bind_param('i',$rid);$s->execute();if(!$s->get_result()->fetch_assoc())redirect('petugas/index.php');
$userId=(int)($_SESSION['user']['id']??0);$s=$conn->prepare("SELECT nama_lengkap FROM users WHERE id=? AND role='petugas' LIMIT 1");$s->bind_param('i',$userId);$s->execute();$namaPetugas=trim((string)($s->get_result()->fetch_assoc()['nama_lengkap']??''));if($namaPetugas===''||mb_strlen($namaPetugas)>120)exit('Nama akun petugas tidak valid. Silakan hubungi admin.');
$statuses=$_POST['status']??[];if(!is_array($statuses)||!$statuses)redirect('petugas/form_ceklist.php?ruangan='.$rid);
$note=trim($_POST['catatan_temuan']??'');$moved=[];$conn->begin_transaction();
try{
 $date=date('Y-m-d');$s=$conn->prepare('INSERT INTO ceklist_harian (ruangan_id,tanggal,catatan_temuan) VALUES (?,?,?) ON DUPLICATE KEY UPDATE catatan_temuan=VALUES(catatan_temuan)');$s->bind_param('iss',$rid,$date,$note);$s->execute();
 $s=$conn->prepare('SELECT id FROM ceklist_harian WHERE ruangan_id=? AND tanggal=?');$s->bind_param('is',$rid,$date);$s->execute();$hid=(int)$s->get_result()->fetch_assoc()['id'];
 $check=$conn->prepare('SELECT COUNT(*) total FROM ceklist_detail WHERE ceklist_harian_id=? AND jam_cek=?');$check->bind_param('is',$hid,$jam);$check->execute();$isEdit=(int)$check->get_result()->fetch_assoc()['total']>0;
 $valid=[];$s=$conn->prepare('SELECT id FROM indikator_ruangan WHERE ruangan_id=?');$s->bind_param('i',$rid);$s->execute();foreach(stmt_rows($s) as $x)$valid[(int)$x['id']]=true;
 if(count($statuses)!==count($valid))throw new RuntimeException('Semua indikator wajib dipilih.');
 $pid=(int)$_SESSION['user']['id'];
 $up=$conn->prepare("INSERT INTO ceklist_detail (ceklist_harian_id,jam_cek,indikator_id,status,foto_bukti,nama_petugas,petugas_id) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),foto_bukti=IF(VALUES(status)='Bersih/Rapi',NULL,COALESCE(VALUES(foto_bukti),foto_bukti)),nama_petugas=VALUES(nama_petugas),petugas_id=VALUES(petugas_id),created_at=CURRENT_TIMESTAMP");
 foreach($statuses as $key=>$status){$iid=(int)$key;if(!isset($valid[$iid])||!in_array($status,['Bersih/Rapi','Kotor/Perlu Tindakan'],true))throw new RuntimeException('Data indikator tidak valid.');
  $photo=null;$f=$_FILES['foto_bukti']??null;
  $oldPhotoStmt=$conn->prepare('SELECT foto_bukti FROM ceklist_detail WHERE ceklist_harian_id=? AND jam_cek=? AND indikator_id=?');$oldPhotoStmt->bind_param('isi',$hid,$jam,$iid);$oldPhotoStmt->execute();$oldPhoto=$oldPhotoStmt->get_result()->fetch_assoc()['foto_bukti']??null;
  if($status==='Kotor/Perlu Tindakan' && (!$f || !isset($f['error'][$iid]) || $f['error'][$iid]===UPLOAD_ERR_NO_FILE) && !$oldPhoto)throw new RuntimeException('Indikator yang perlu tindakan wajib dilengkapi foto bukti.');
  if($status==='Kotor/Perlu Tindakan' && $f && isset($f['error'][$iid]) && $f['error'][$iid]!==UPLOAD_ERR_NO_FILE){$err=$f['error'][$iid];if($err!==UPLOAD_ERR_OK)throw new RuntimeException('Upload foto indikator gagal.');$tmp=$f['tmp_name'][$iid];$size=(int)$f['size'][$iid];if($size>5*1024*1024)throw new RuntimeException('Ukuran foto setelah kompresi maksimal 5 MB. Silakan pilih ulang foto.');$mime=(new finfo(FILEINFO_MIME_TYPE))->file($tmp);$allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp'];if(!isset($allowed[$mime])||!getimagesize($tmp))throw new RuntimeException('Foto harus JPG, PNG, atau WEBP.');$filename=bin2hex(random_bytes(16)).'.'.$allowed[$mime];$dest=__DIR__.'/../uploads/bukti/'.$filename;if(!move_uploaded_file($tmp,$dest))throw new RuntimeException('Gagal menyimpan foto ke server.');$moved[]=$dest;$photo='uploads/bukti/'.$filename;}
  $up->bind_param('isisssi',$hid,$jam,$iid,$status,$photo,$namaPetugas,$pid);$up->execute();
 }
 $conn->commit();redirect('petugas/index.php?saved=1&jam='.urlencode($jam).($isEdit?'&updated=1':''));
}catch(Throwable $ex){$conn->rollback();foreach($moved as $file)if(is_file($file))@unlink($file);error_log('[E-Ceklist] Gagal simpan ceklist: '.$ex->getMessage());http_response_code(400);exit('Gagal menyimpan ceklist: '.e($ex->getMessage()));}
