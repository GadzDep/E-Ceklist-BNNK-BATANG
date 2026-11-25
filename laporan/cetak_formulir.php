<?php
require_once __DIR__.'/../config/koneksi.php';require_login();
$rid=(int)($_GET['ruangan']??0);$date=$_GET['tanggal']??date('Y-m-d');
$s=$conn->prepare('SELECT r.*,h.id hid,h.catatan_pj_sarpras,h.catatan_kasubbag_umum,h.no_polisi,h.verif_pj_sarpras,h.waktu_verif_pj,h.nama_verifikator_pj,h.verif_kasubbag,h.waktu_verif_kasubbag,h.nama_verifikator_kasubbag FROM ruangan r LEFT JOIN ceklist_harian h ON h.ruangan_id=r.id AND h.tanggal=? WHERE r.id=?');$s->bind_param('si',$date,$rid);$s->execute();$room=$s->get_result()->fetch_assoc();if(!$room)exit('Ruangan tidak ditemukan.');
$isVehicle=strtolower((string)$room['kode_qr']==='kendaraan-01'||str_contains(mb_strtolower($room['nama_ruangan']),'kendaraan'));
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? AND is_active=1 ORDER BY urutan');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);
$times=[];$ts=$conn->prepare('SELECT jam_cek FROM jadwal_jam_ruangan WHERE ruangan_id=? ORDER BY urutan');$ts->bind_param('i',$rid);$ts->execute();foreach(stmt_rows($ts) as $t)$times[]=$t['jam_cek'];if(!$times)$times=['08:00','11:00','14:00'];
$vehicles=$isVehicle?$conn->query('SELECT id,nama FROM kendaraan ORDER BY id')->fetch_all(MYSQLI_ASSOC):[];
$plates=[];if($isVehicle&&$room['hid']){$s=$conn->prepare('SELECT kendaraan_id,no_polisi FROM ceklist_harian_kendaraan WHERE ceklist_harian_id=?');$s->bind_param('i',$room['hid']);$s->execute();foreach(stmt_rows($s) as $v)$plates[(int)$v['kendaraan_id']]=$v['no_polisi'];}
$details=[];if($room['hid']){$hid=(int)$room['hid'];$s=$conn->prepare('SELECT indikator_id,jam_cek,status,foto_bukti,nama_petugas,kendaraan_id FROM ceklist_detail WHERE ceklist_harian_id=?');$s->bind_param('i',$hid);$s->execute();foreach(stmt_rows($s) as $d)$details[(int)$d['kendaraan_id']][$d['jam_cek']][(int)$d['indikator_id']]=$d;}
$signatureNames=['petugas'=>'','pj_sarpras'=>'','kasubbag_umum'=>''];$s=$conn->prepare("SELECT role,nama_lengkap FROM users WHERE role IN ('petugas','pj_sarpras','kasubbag_umum')");$s->execute();foreach(stmt_rows($s) as $person)$signatureNames[$person['role']]=$person['nama_lengkap'];
$printGroups=$isVehicle?$vehicles:[['id'=>0,'nama'=>'']];$evidencePhotos=[];foreach($printGroups as $vg){$vid=(int)$vg['id'];foreach($times as $photoTime)foreach($items as $photoItem){$photoEntry=$details[$vid][$photoTime][(int)$photoItem['id']]??null;$photoPath=$photoEntry['foto_bukti']??'';if(($photoEntry['status']??'')==='Tidak'&&$photoPath&&is_file(__DIR__.'/../'.$photoPath))$evidencePhotos[]=['vehicle'=>$vg['nama'],'plate'=>$plates[$vid]??'','time'=>$photoTime,'indicator'=>$photoItem['nama_indikator'],'path'=>$photoPath];}}
$from=$_GET['from']??'';if(current_role()==='petugas')$backUrl='../petugas/index.php';elseif($from==='detail')$backUrl='../verifikator/detail_ceklist.php?ruangan='.$rid.'&tanggal='.urlencode($date);else $backUrl='../verifikator/dashboard.php?tanggal='.urlencode($date);
$months=[1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
function print_table($items,$times,$details,$vid){ ?>
<table class="report">
<thead>
<tr><th rowspan="2" class="col-no">No</th><th rowspan="2" class="col-item">Item Pemeriksaan</th><th colspan="<?= count($times) ?>" class="jam-cek-heading">Jam Cek</th><th rowspan="2" class="col-paraf">Paraf Petugas</th></tr>
<tr><?php foreach($times as $jam): ?><th class="time-heading"><?= e($jam) ?></th><?php endforeach; ?></tr>
</thead>
<tbody><?php foreach($items as $index=>$it): ?><tr><td><?= $index+1 ?></td><td class="item-name"><?= e($it['nama_indikator']) ?></td><?php foreach($times as $jam):$entry=$details[$vid][$jam][(int)$it['id']]??null;$status=$entry['status']??''; ?><td class="report-indicator-cell"><?= $status==='Ya'?'✓':($status==='Tidak'?'✗':'') ?></td><?php endforeach; ?><td><?= array_filter(array_map(fn($j)=>$details[$vid][$j][(int)$it['id']]??null,$times))?'✓':'' ?></td></tr><?php endforeach; ?></tbody>
</table><?php }

?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title></title><link rel="stylesheet" href="../assets/css/print.css"></head><body>
<nav class="print-actions" aria-label="Menu cetak">
  <div class="print-actions-copy">
    <span class="print-actions-eyebrow">DOKUMEN CHECKLIST</span>
    <strong>Pratinjau Formulir</strong>
    <span>Periksa dokumen sebelum mencetak atau menyimpannya sebagai PDF.</span>
  </div>
  <div class="print-actions-buttons">
    <button type="button" onclick="window.print()" class="action-btn action-print">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 8V3h10v5M7 17H5a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2M7 14h10v7H7z"/><path d="M17 11h.01"/></svg>
      <span>Cetak / Simpan PDF</span>
    </button>
    <a class="action-btn action-exit" href="<?= e($backUrl) ?>">
      <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10 17l5-5-5-5M15 12H3"/><path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/></svg>
      <span>Keluar</span>
    </a>
  </div>
</nav>
<?php
$roomTitle=preg_replace('/\s*\([^)]*\)\s*$/u','',(string)$room['nama_ruangan']);
$roomSubtitle='';
if(preg_match('/\(([^()]*)\)\s*$/u',(string)$room['nama_ruangan'],$subtitleMatch))$roomSubtitle='('.$subtitleMatch[1].')';
?>
<div class="sheet">
<header class="document-heading">
<h1>FORMULIR CEKLIST KEBERSIHAN <?= e(strtoupper($roomTitle)) ?></h1>
<?php if($roomSubtitle!==''): ?><div class="document-subtitle"><?= e(strtoupper($roomSubtitle)) ?></div><?php endif; ?>
<div class="document-agency">BNNK BATANG</div>
</header>
<div class="date-row"><strong>Tanggal:</strong><span><?= e(tanggal_indo($date)) ?></span></div>
<?php if($isVehicle): foreach($vehicles as $v):$vid=(int)$v['id']; ?><section class="vehicle-report-section"><h3><?= e($v['nama']) ?><?= !empty($plates[$vid])?' · No. Polisi: '.e($plates[$vid]):'' ?></h3><?php print_table($items,$times,$details,$vid); ?></section><?php endforeach; else: print_table($items,$times,$details,0); endif; ?>
<?php if($evidencePhotos): ?><section class="evidence-section"><h3 class="evidence-title">Dokumentasi Foto Bukti</h3><div class="evidence-grid"><?php foreach($evidencePhotos as $photoData): ?><div class="evidence-card"><img class="evidence-photo" src="<?= e(base_url($photoData['path'])) ?>" alt="Foto bukti"><div class="evidence-caption"><strong><?= e($photoData['indicator']) ?></strong><?php if($photoData['vehicle']): ?><?= e($photoData['vehicle']) ?><?= $photoData['plate']?' · '.e($photoData['plate']):'' ?><br><?php endif; ?>Waktu pemeriksaan: <?= e($photoData['time']) ?></div></div><?php endforeach; ?></div></section><?php endif; ?>
<div class="notes"><strong>Catatan PJ Sarpras:</strong><div class="notes-content"><?= trim((string)($room['catatan_pj_sarpras']??''))!==''?nl2br(e($room['catatan_pj_sarpras'])):'Tidak ada catatan.' ?></div></div><div class="notes"><strong>Catatan Kasubbag Umum:</strong><div class="notes-content"><?= trim((string)($room['catatan_kasubbag_umum']??''))!==''?nl2br(e($room['catatan_kasubbag_umum'])):'Tidak ada catatan.' ?></div></div>
<div class="signatures"><div><strong>Petugas Kebersihan</strong><div class="signature-space"></div><span><?= e($signatureNames['petugas']) ?></span></div><div><strong>PJ Sarpras</strong><div class="signature-space"></div><span><?= e($signatureNames['pj_sarpras']) ?></span></div><div><strong>Kasubbag Umum</strong><div class="signature-space"></div><span><?= e($signatureNames['kasubbag_umum']) ?></span></div></div></div><script>
(function(){ if(new URLSearchParams(location.search).has("print")){ setTimeout(()=>window.print(),250); } })();
</script></body></html>
