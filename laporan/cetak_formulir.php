<?php
require_once __DIR__.'/../config/koneksi.php';require_login();
$rid=(int)($_GET['ruangan']??0);$date=$_GET['tanggal']??date('Y-m-d');
$s=$conn->prepare('SELECT r.*,h.id hid,h.catatan_temuan,h.no_polisi,h.verif_pj_sarpras,h.waktu_verif_pj,h.nama_verifikator_pj,h.verif_kasubbag,h.waktu_verif_kasubbag,h.nama_verifikator_kasubbag FROM ruangan r LEFT JOIN ceklist_harian h ON h.ruangan_id=r.id AND h.tanggal=? WHERE r.id=?');$s->bind_param('si',$date,$rid);$s->execute();$room=$s->get_result()->fetch_assoc();if(!$room)exit('Ruangan tidak ditemukan.');
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? AND is_active=1 ORDER BY urutan');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);$times=[];$ts=$conn->prepare('SELECT jam_cek FROM jadwal_jam_ruangan WHERE ruangan_id=? ORDER BY urutan');$ts->bind_param('i',$rid);$ts->execute();foreach(stmt_rows($ts) as $t)$times[]=$t['jam_cek'];if(!$times)$times=['08:00','11:00','14:00'];$details=[];
if($room['hid']){$hid=(int)$room['hid'];$s=$conn->prepare('SELECT indikator_id,jam_cek,status,foto_bukti,nama_petugas FROM ceklist_detail WHERE ceklist_harian_id=?');$s->bind_param('i',$hid);$s->execute();foreach(stmt_rows($s) as $d)$details[$d['jam_cek']][(int)$d['indikator_id']]=$d;}

$signatureNames=['petugas'=>'','pj_sarpras'=>'','kasubbag_umum'=>''];
$s=$conn->prepare("SELECT role,nama_lengkap FROM users WHERE role IN ('petugas','pj_sarpras','kasubbag_umum')");$s->execute();foreach(stmt_rows($s) as $person)$signatureNames[$person['role']]=$person['nama_lengkap'];
$evidencePhotos=[];
foreach($times as $photoTime){foreach($items as $photoItem){$photoEntry=$details[$photoTime][(int)$photoItem['id']]??null;$photoPath=$photoEntry['foto_bukti']??'';if(($photoEntry['status']??'')==='Kotor/Perlu Tindakan' && $photoPath && is_file(__DIR__.'/../'.$photoPath)) $evidencePhotos[]=['time'=>$photoTime,'indicator'=>$photoItem['nama_indikator'],'path'=>$photoPath];}}
$from=$_GET['from']??'';
if(current_role()==='petugas') $backUrl='../petugas/index.php';
elseif($from==='detail') $backUrl='../verifikator/detail_ceklist.php?ruangan='.(int)$rid.'&tanggal='.urlencode($date);
else $backUrl='../verifikator/dashboard.php?tanggal='.urlencode($date);
$months=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Formulir Ceklist</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="../assets/css/print.css"><style>.paraf-mark{display:inline-block;min-width:28px;font-family:"Segoe Script","Brush Script MT",cursive;font-style:italic;font-weight:bold;font-size:17px;letter-spacing:1px;transform:rotate(-8deg);color:#172554}.staff-signature{font-weight:600;font-style:italic;display:flex;align-items:end;justify-content:center;padding-bottom:3px}.signatures span{display:block;font-weight:600;margin-top:2px}.signature-space{height:48px}.report-indicator-cell{min-width:36px}.evidence-section{margin-top:12px}.evidence-title{font-size:10px;font-weight:bold;margin:0 0 7px}.evidence-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px}.evidence-card{border:1px solid #777;padding:6px;display:flex;gap:8px;align-items:flex-start;page-break-inside:avoid;break-inside:avoid}.evidence-photo{width:125px;height:95px;object-fit:contain;border:1px solid #aaa;padding:2px;flex-shrink:0}.evidence-caption{font-size:9px;line-height:1.4}.evidence-caption strong{display:block;margin-bottom:3px}</style></head><body>
<div class="print-actions"><button onclick="window.print()" class="btn btn-primary">Cetak / Simpan PDF</button> <a class="btn btn-outline-secondary" href="<?= e($backUrl) ?>">Kembali</a></div>
<div class="sheet"><h2>FORMULIR CEKLIST KEBERSIHAN DAN KERAPIAN <?= e(strtoupper($room['nama_ruangan'])) ?></h2>
<div class="meta"><span>Tanggal : <?= date('d',strtotime($date)) ?></span><span>Bulan : <?= e($months[(int)date('n',strtotime($date))]) ?></span><span>Tahun : <?= date('Y',strtotime($date)) ?></span></div>
<?php if(str_contains(mb_strtolower($room['nama_ruangan']),'kendaraan') && !empty($room['no_polisi'])): ?><div class="meta"><span>No. Polisi Kendaraan: <?= e($room['no_polisi']) ?></span></div><?php endif; ?>
<table class="table table-bordered text-center align-middle report"><thead><tr><th>No</th><th>Item Pemeriksaan</th><?php foreach($times as $jam): ?><th><?= e($jam) ?></th><?php endforeach; ?><th>Paraf Petugas</th><th>Paraf Pengawas</th></tr></thead><tbody>
<?php foreach($items as $index=>$it): ?><tr><td><?= $index+1 ?></td><td class="text-start"><?= e($it['nama_indikator']) ?></td><?php foreach($times as $jam):$entry=$details[$jam][(int)$it['id']]??null;$st=$entry['status']??''; ?><td class="report-indicator-cell"><?= $st==='Bersih/Rapi'?'✓':($st==='Kotor/Perlu Tindakan'?'✗':'-') ?></td><?php endforeach; ?><td><?= array_filter(array_map(fn($j)=>$details[$j][(int)$it['id']]??null,$times))?'✓':'-' ?></td><td><?= !empty($room['verif_pj_sarpras'])?'✓':'-' ?></td></tr><?php endforeach; ?>
</tbody></table>
<div class="report-verification-status mt-2 small"><strong>Status Verifikasi:</strong> PJ Sarpras <?= !empty($room['verif_pj_sarpras'])?'✓ Terverifikasi':'- Belum' ?> &nbsp; | &nbsp; Kasubbag Umum <?= !empty($room['verif_kasubbag'])?'✓ Terverifikasi':'- Belum' ?></div>
<?php if($evidencePhotos): ?><section class="evidence-section"><h3 class="evidence-title">Dokumentasi Foto Bukti</h3><div class="evidence-grid"><?php foreach($evidencePhotos as $photoData): ?><div class="evidence-card"><img class="evidence-photo" src="<?= e(base_url($photoData['path'])) ?>" alt="Foto bukti"><div class="evidence-caption"><strong><?= e($photoData['indicator']) ?></strong>Waktu pemeriksaan: <?= e($photoData['time']) ?></div></div><?php endforeach; ?></div></section><?php endif; ?>
<div class="notes"><strong>Catatan:</strong><div class="notes-content"><?= nl2br(e($room['catatan_temuan']??'')) ?></div></div>
<div class="signatures">
<div><strong>Petugas Kebersihan</strong><div class="signature-space"></div><span><?= e($signatureNames['petugas']) ?></span></div>
<div><strong>PJ Sarpras</strong><div class="signature-space"></div><span><?= e($signatureNames['pj_sarpras']) ?></span></div>
<div><strong>Kasubbag Umum</strong><div class="signature-space"></div><span><?= e($signatureNames['kasubbag_umum']) ?></span></div>
</div>
</div><script>if(new URLSearchParams(location.search).has('print'))window.print()</script></body></html>
