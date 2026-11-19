<?php
require_once __DIR__.'/../config/koneksi.php';require_login();
$rid=(int)($_GET['ruangan']??0);$date=$_GET['tanggal']??date('Y-m-d');
$s=$conn->prepare('SELECT r.*,h.id hid,h.catatan_temuan,h.verif_pj_sarpras,h.waktu_verif_pj,h.nama_verifikator_pj,h.verif_kasubbag,h.waktu_verif_kasubbag,h.nama_verifikator_kasubbag FROM ruangan r LEFT JOIN ceklist_harian h ON h.ruangan_id=r.id AND h.tanggal=? WHERE r.id=?');$s->bind_param('si',$date,$rid);$s->execute();$room=$s->get_result()->fetch_assoc();if(!$room)exit('Ruangan tidak ditemukan.');
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? ORDER BY urutan');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);$details=[];
if($room['hid']){$hid=(int)$room['hid'];$s=$conn->prepare('SELECT indikator_id,jam_cek,status,nama_petugas FROM ceklist_detail WHERE ceklist_harian_id=?');$s->bind_param('i',$hid);$s->execute();foreach(stmt_rows($s) as $d)$details[$d['jam_cek']][(int)$d['indikator_id']]=$d;}

$staffNames=[];
if($room['hid']){
    $hid=(int)$room['hid'];
    $s=$conn->prepare('SELECT DISTINCT nama_petugas FROM ceklist_detail WHERE ceklist_harian_id=? AND nama_petugas IS NOT NULL AND nama_petugas<>\'\' ORDER BY nama_petugas');
    $s->bind_param('i',$hid);$s->execute();
    foreach(stmt_rows($s) as $person){$staffNames[]=$person['nama_petugas'];}
}
$staffNameText=implode(', ', $staffNames);
$from=$_GET['from']??'';
if(current_role()==='petugas') $backUrl='../petugas/index.php';
elseif($from==='detail') $backUrl='../verifikator/detail_ceklist.php?ruangan='.(int)$rid.'&tanggal='.urlencode($date);
else $backUrl='../verifikator/dashboard.php?tanggal='.urlencode($date);
$months=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Formulir Ceklist</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/print.css"><style>.paraf-mark{display:inline-block;min-width:28px;font-family:"Segoe Script","Brush Script MT",cursive;font-style:italic;font-weight:bold;font-size:17px;letter-spacing:1px;transform:rotate(-8deg);color:#172554}.staff-signature{font-weight:600;font-style:italic;display:flex;align-items:end;justify-content:center;padding-bottom:3px}</style></head><body>
<div class="print-actions"><button onclick="window.print()" class="btn btn-primary">Cetak / Simpan PDF</button> <a class="btn btn-outline-secondary" href="<?= e($backUrl) ?>">Kembali</a></div>
<div class="sheet"><h2>FORMULIR CEKLIST KEBERSIHAN DAN KERAPIAN <?= e(strtoupper($room['nama_ruangan'])) ?></h2>
<div class="meta"><span>Tanggal : <?= date('d',strtotime($date)) ?></span><span>Bulan : <?= e($months[(int)date('n',strtotime($date))]) ?></span><span>Tahun : <?= date('Y',strtotime($date)) ?></span></div>
<table class="table table-bordered text-center align-middle report"><thead><tr><th>No</th><th>Waktu Cek</th><?php foreach($items as $it): ?><th><?= e(preg_replace('/\s*\/\s*/u', ' & ', $it['nama_indikator'])) ?></th><?php endforeach; ?><th>Paraf Petugas</th><th>Verifikasi PJ Sarpras</th><th>Verifikasi Kasubbag Umum</th></tr></thead><tbody>
<?php foreach(['08:00','11:00','14:00'] as $jam): ?><tr><td><?= array_search($jam,['08:00','11:00','14:00'])+1 ?></td><td><?= e($jam) ?></td><?php foreach($items as $it):$entry=$details[$jam][(int)$it['id']]??null;$st=$entry['status']??''; ?><td><?= $st==='Bersih/Rapi'?'✓':($st==='Kotor/Perlu Tindakan'?'✗':'-') ?></td><?php endforeach; ?><td><?= !empty($details[$jam])?'✓':'-' ?></td><td><?= !empty($room['verif_pj_sarpras'])?'✓':'-' ?></td><td><?= !empty($room['verif_kasubbag'])?'✓':'-' ?></td></tr><?php endforeach; ?>
</tbody></table>
<div class="notes"><strong>Catatan:</strong><div class="notes-content"><?= nl2br(e($room['catatan_temuan']??'')) ?></div></div>
<div class="signatures">
<div><strong>Petugas Kebersihan</strong><div class="signature-space staff-signature"><?= e($staffNameText!==''?$staffNameText:'') ?></div>______________________</div>
<div><strong>PJ Sarpras</strong><div class="signature-space"></div><?php if($room['waktu_verif_pj']): ?><b>TERVERIFIKASI DIGITAL</b><br><?= e($room['nama_verifikator_pj']??'') ?><br><small><?= e($room['waktu_verif_pj']) ?></small><?php else: ?>______________________<?php endif; ?></div>
<div><strong>Kasubbag Umum</strong><div class="signature-space"></div><?php if($room['waktu_verif_kasubbag']): ?><b>TERVERIFIKASI DIGITAL</b><br><?= e($room['nama_verifikator_kasubbag']??'') ?><br><small><?= e($room['waktu_verif_kasubbag']) ?></small><?php else: ?>______________________<?php endif; ?></div>
</div>
</div><script>if(new URLSearchParams(location.search).has('print'))window.print()</script></body></html>
