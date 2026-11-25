<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['pj_sarpras','kasubbag_umum','admin']);
$rid=(int)($_GET['ruangan']??0);$date=$_GET['tanggal']??date('Y-m-d');
$s=$conn->prepare('SELECT r.*,h.id hid,h.catatan_pj_sarpras,h.catatan_kasubbag_umum,h.no_polisi,h.verif_pj_sarpras,h.waktu_verif_pj,h.nama_verifikator_pj,h.verif_kasubbag,h.waktu_verif_kasubbag,h.nama_verifikator_kasubbag FROM ruangan r LEFT JOIN ceklist_harian h ON h.ruangan_id=r.id AND h.tanggal=? WHERE r.id=?');
$s->bind_param('si',$date,$rid);$s->execute();$room=$s->get_result()->fetch_assoc();if(!$room)redirect('verifikator/dashboard.php');
$roleNames=['pj_sarpras'=>'','kasubbag_umum'=>''];$s=$conn->prepare("SELECT role,nama_lengkap FROM users WHERE role IN ('pj_sarpras','kasubbag_umum')");$s->execute();foreach(stmt_rows($s) as $n)$roleNames[$n['role']]=$n['nama_lengkap'];
$data=[];if($room['hid']){$hid=(int)$room['hid'];$s=$conn->prepare('SELECT d.*,i.nama_indikator,u.nama_lengkap FROM ceklist_detail d JOIN indikator_ruangan i ON i.id=d.indikator_id JOIN users u ON u.id=d.petugas_id WHERE d.ceklist_harian_id=? ORDER BY d.kendaraan_id,d.jam_cek,i.urutan');$s->bind_param('i',$hid);$s->execute();foreach(stmt_rows($s) as $x)$data[(int)$x['kendaraan_id']][$x['jam_cek']][]=$x;}
$isVehicle=strtolower((string)$room['kode_qr'])==='kendaraan-01'||str_contains(mb_strtolower($room['nama_ruangan']),'kendaraan');$vehicles=$isVehicle?$conn->query('SELECT id,nama FROM kendaraan ORDER BY id')->fetch_all(MYSQLI_ASSOC):[];$vehicleId=$isVehicle?(int)($_GET['kendaraan']??1):0;if($isVehicle&&!in_array($vehicleId,array_column($vehicles,'id'))) $vehicleId=1;$vehiclePlate='';if($isVehicle&&$room['hid']){$ps=$conn->prepare('SELECT no_polisi FROM ceklist_harian_kendaraan WHERE ceklist_harian_id=? AND kendaraan_id=?');$ps->bind_param('ii',$room['hid'],$vehicleId);$ps->execute();$vehiclePlate=(string)($ps->get_result()->fetch_assoc()['no_polisi']??'');}
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? AND is_active=1 ORDER BY urutan');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);
function url_foto_bukti(?string $path): string {
    $path=trim((string)$path);
    if($path==='') return '';
    $path=str_replace('\\','/',$path);
    // Path lama mungkin tersimpan dengan awalan ./ atau slash.
    $path=preg_replace('~^(\./|/)+~','',$path);
    // Hanya izinkan file dari folder bukti, bukan path filesystem server.
    $marker='uploads/bukti/';
    $pos=strpos($path,$marker);
    if($pos!==false) $path=substr($path,$pos);
    elseif(str_starts_with($path,'bukti/')) $path='uploads/'.$path;
    elseif(!str_starts_with($path,'uploads/')) $path='uploads/bukti/'.basename($path);
    return base_url($path);
}
$times=[];$ts=$conn->prepare('SELECT jam_cek FROM jadwal_jam_ruangan WHERE ruangan_id=? ORDER BY urutan');$ts->bind_param('i',$rid);$ts->execute();foreach(stmt_rows($ts) as $t)$times[]=$t['jam_cek'];if(!$times)$times=['08:00','11:00','14:00'];
$page_title='Detail Ceklist';include __DIR__.'/../includes/header.php';
?>
<a href="<?= e(base_url('verifikator/dashboard.php?tanggal='.$date)) ?>" class="btn btn-light btn-back-rekap"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
<?php if(($_GET['verifikasi']??'')==='success'): ?><div class="alert alert-success border-0 rounded-4 mt-3"><i class="bi bi-check-circle-fill me-2"></i><?= ($_GET['jenis']??'')==='pj'?'Ceklist berhasil diverifikasi oleh PJ Sarpras.':'Ceklist berhasil diverifikasi oleh Kasubbag Umum.' ?></div><?php endif; ?>
<h1 class="h3 page-heading mt-3"><?= e(preg_replace('/\s*\([^)]*\)\s*$/u','',(string)$room['nama_ruangan'])) ?></h1><div class="report-date-banner mb-4"><span class="report-date-icon"><i class="bi bi-calendar2-week"></i></span><div><small>TANGGAL LAPORAN</small><strong><?= e(tanggal_indo($date)) ?></strong><span>Tanggal pemeriksaan yang sedang ditinjau</span></div><span class="report-date-tag">Tanggal dipilih</span></div><?php if($isVehicle): ?><div class="vehicle-selector-grid mb-4"><?php foreach($vehicles as $v): $active=(int)$v['id']===$vehicleId; ?><a class="vehicle-selector-card <?= $active?'active':'' ?>" href="<?= e(base_url('verifikator/detail_ceklist.php?ruangan='.$rid.'&tanggal='.urlencode($date).'&kendaraan='.$v['id'])) ?>"><span class="vehicle-selector-icon"><i class="bi bi-car-front-fill"></i></span><span class="vehicle-selector-info"><small>KENDARAAN <?= (int)$v['id'] ?></small><strong><?= e($v['nama']) ?></strong><em><?= $active?e($vehiclePlate?:'Nomor polisi belum diisi'):'Lihat detail kendaraan' ?></em></span><span class="vehicle-selector-arrow"><i class="bi bi-arrow-up-right-circle-fill"></i></span></a><?php endforeach; ?></div><?php endif; ?><div class="mb-3"><a class="btn btn-outline-primary" href="<?= e(base_url('laporan/cetak_formulir.php?ruangan='.$rid.'&tanggal='.urlencode($date).'&from=detail')) ?>"><i class="bi bi-printer me-1"></i>Cetak Formulir</a></div>
<style>
.report-date-banner{display:flex;align-items:center;gap:15px;padding:17px 20px;border-radius:20px;background:linear-gradient(120deg,#102f67,#24539a);color:#fff;box-shadow:0 12px 28px rgba(16,47,103,.18);position:relative;overflow:hidden}.report-date-icon{width:52px;height:52px;flex:0 0 52px;border-radius:16px;display:grid;place-items:center;background:rgba(255,255,255,.15);font-size:1.55rem}.report-date-banner small{display:block;font-size:.68rem;letter-spacing:.14em;font-weight:800;color:#c9d9f3;margin-bottom:3px}.report-date-banner strong{display:block;font-size:1.18rem;line-height:1.35}.report-date-banner div span{display:block;font-size:.78rem;color:#d7e4f8;margin-top:3px}.report-date-tag{margin-left:auto;padding:7px 11px;border-radius:999px;background:rgba(255,255,255,.13);font-size:.72rem;font-weight:700;white-space:nowrap}.vehicle-selector-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:13px}.vehicle-selector-card{display:flex;align-items:center;gap:12px;min-width:0;padding:15px;border:1px solid #e3e9f3;border-radius:18px;background:linear-gradient(145deg,#fff,#f7f9fd);color:#243b60;text-decoration:none;box-shadow:0 5px 16px rgba(20,45,85,.05);transition:.18s}.vehicle-selector-card:hover{transform:translateY(-2px);border-color:#9db7df;box-shadow:0 10px 22px rgba(20,45,85,.1);color:#173e79}.vehicle-selector-card.active{border:2px solid #315fa5;background:linear-gradient(145deg,#edf4ff,#fff);box-shadow:0 8px 22px rgba(37,82,145,.13)}.vehicle-selector-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border-radius:15px;background:#e8effa;color:#315fa5;font-size:1.35rem}.vehicle-selector-card.active .vehicle-selector-icon{background:#315fa5;color:white}.vehicle-selector-info{display:block;min-width:0}.vehicle-selector-info small{display:block;color:#71819b;font-size:.63rem;font-weight:800;letter-spacing:.1em}.vehicle-selector-info strong{display:block;font-size:.93rem;line-height:1.3}.vehicle-selector-info em{display:block;margin-top:4px;color:#78869b;font-size:.72rem;font-style:normal;overflow-wrap:anywhere}.vehicle-selector-arrow{margin-left:auto;color:#91a4c0;font-size:1.1rem}.vehicle-selector-card.active .vehicle-selector-arrow{color:#315fa5}@media(max-width:767.98px){.vehicle-selector-grid{grid-template-columns:1fr}.report-date-tag{display:none}.report-date-banner{padding:15px}.report-date-banner strong{font-size:1.05rem}}.btn-back-rekap{display:inline-flex;align-items:center;border:1px solid rgba(11,46,109,.10);background:rgba(255,255,255,.78);color:#53627a;border-radius:14px;padding:.62rem 1rem;font-weight:600;box-shadow:0 5px 16px rgba(11,46,109,.055);transition:background .18s,transform .18s}.btn-back-rekap:hover{background:#edf1f7;color:#263c5d;transform:translateX(-2px)}.detail-table-wrap{overflow:hidden;border-radius:16px}.detail-table{margin:0;border-collapse:separate;border-spacing:0}.detail-table thead th{background:#eef3fb;color:#24446f;font-size:.78rem;font-weight:800;letter-spacing:.025em;padding:.9rem .85rem;border-bottom:1px solid #dce5f1;white-space:nowrap}.detail-table tbody td{vertical-align:middle;padding:.85rem;border-bottom:1px solid #edf1f6;background:#fff}.detail-table tbody tr:last-child td{border-bottom:0}.detail-table tbody tr:hover td{background:#f8faff}.detail-table td:nth-child(2){min-width:95px}.detail-table td:nth-child(3){color:#5e6c82;font-size:.88rem}.detail-table .badge{padding:.45rem .7rem;border-radius:999px;font-size:.76rem;font-weight:750}.detail-table td:first-child{font-weight:600;color:#34334c;min-width:190px;white-space:normal;overflow-wrap:break-word;word-break:normal}.detail-mobile-list{display:none}.detail-mobile-item{background:#fff;border:1px solid #e5ebf3;border-radius:17px;padding:16px;margin-bottom:11px;min-width:0;box-shadow:0 5px 16px rgba(24,48,83,.045)}.detail-mobile-title{font-weight:650;color:#34334c;line-height:1.4;overflow-wrap:break-word;word-break:normal;margin-bottom:10px}.detail-mobile-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}.detail-mobile-meta small{display:block;color:#85879a;font-size:.7rem;margin-bottom:4px}.detail-mobile-meta strong{font-size:.83rem;overflow-wrap:anywhere}
@media(max-width:767.98px){.detail-desktop-table{display:none}.detail-mobile-list{display:block}.detail-mobile-meta{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}.detail-mobile-item .btn{padding:.45rem .7rem}}
.detail-table-wrap{border:1px solid #e3eaf3;box-shadow:0 6px 18px rgba(25,48,82,.045)}
.detail-table td:first-child{color:#273d5d;font-weight:600}
.detail-mobile-title{color:#263f63;font-size:.93rem}
.detail-mobile-meta>div{padding:9px 10px;border-radius:11px;background:#f6f8fc;border:1px solid #edf1f6}
.verification-note-read{padding:12px 14px;border-radius:12px;background:#f4f7fc;border:1px solid #e6edf6;color:#42536c}
.verification-note-read strong{display:block;color:#24446f;margin-bottom:4px}
@media(max-width:767.98px){.verification-card{padding:15px}.verification-form .btn{width:100%}}
</style>
<?php foreach($times as $jam): ?>
<?php
$map=[];
$petugasUntukJam=[];
foreach($data[$vehicleId][$jam]??[] as $d){
    $map[(int)$d['indikator_id']]=$d;
    $namaPetugas=trim((string)($d['nama_petugas']??$d['nama_lengkap']??''));
    if($namaPetugas!=='') $petugasUntukJam[$namaPetugas]=$namaPetugas;
}
$labelPetugas=$petugasUntukJam?implode(', ',array_values($petugasUntukJam)):'Petugas belum tercatat';
?>
<section class="inspection-section mb-4">
    <div class="inspection-heading">
        <div class="inspection-heading-main">
            <span class="inspection-heading-icon"><i class="bi bi-clipboard2-check-fill"></i></span>
            <div>
                <span class="inspection-eyebrow">HASIL PENGECEKAN</span>
                <h2>Jam <?= e($jam) ?></h2>
                <div class="inspection-officer"><i class="bi bi-person-check-fill"></i><span>Oleh</span><strong><?= e($labelPetugas) ?></strong></div>
            </div>
        </div>
        <span class="inspection-time-chip"><i class="bi bi-clock-fill"></i><?= e($jam) ?></span>
    </div>
    <div class="detail-desktop-table">
        <div class="detail-table-wrap">
            <table class="table table-sm align-middle detail-table inspection-result-table">
                <thead><tr><th>Indikator Pemeriksaan</th><th>Status</th><th>Foto Bukti</th></tr></thead>
                <tbody>
                <?php foreach($items as $it): $d=$map[(int)$it['id']]??null; ?>
                    <tr>
                        <td><?= e($it['nama_indikator']) ?></td>
                        <td>
                            <?php if($d): ?><span class="badge <?= $d['status']==='Ya'?'text-bg-success':'text-bg-danger' ?> status-result-badge"><i class="bi <?= $d['status']==='Ya'?'bi-check-circle-fill':'bi-x-circle-fill' ?> me-1"></i><?= $d['status']==='Ya'?'Ya':'Tidak' ?></span>
                            <?php else: ?><span class="text-secondary">Belum diisi</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if($d&&$d['foto_bukti']): ?><button class="btn btn-sm btn-outline-primary inspection-photo-button" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= e(url_foto_bukti($d['foto_bukti'])) ?>"><i class="bi bi-image me-1"></i>Lihat Foto</button>
                            <?php else: ?><span class="text-secondary">—</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <div class="detail-mobile-list inspection-mobile-list">
        <?php foreach($items as $it): $d=$map[(int)$it['id']]??null; ?>
            <div class="detail-mobile-item inspection-mobile-item">
                <div class="detail-mobile-title"><?= e($it['urutan'].'. '.$it['nama_indikator']) ?></div>
                <div class="inspection-mobile-meta">
                    <div><small>Status</small>
                        <?php if($d): ?><span class="badge <?= $d['status']==='Ya'?'text-bg-success':'text-bg-danger' ?> status-result-badge"><i class="bi <?= $d['status']==='Ya'?'bi-check-circle-fill':'bi-x-circle-fill' ?> me-1"></i><?= $d['status']==='Ya'?'Ya':'Tidak' ?></span>
                        <?php else: ?><strong class="text-secondary">Belum diisi</strong><?php endif; ?>
                    </div>
                    <div><small>Foto Bukti</small>
                        <?php if($d&&$d['foto_bukti']): ?><button class="btn btn-sm btn-outline-primary inspection-photo-button" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= e(url_foto_bukti($d['foto_bukti'])) ?>"><i class="bi bi-image me-1"></i>Lihat Foto</button>
                        <?php else: ?><strong class="text-secondary">—</strong><?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
<style>
.inspection-section{border:1px solid #e3eaf3;border-radius:20px;background:#fff;padding:16px;box-shadow:0 8px 24px rgba(28,52,88,.055);overflow:hidden}
.inspection-heading{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:4px 3px 15px;margin-bottom:14px;border-bottom:1px solid #e9eef5}
.inspection-heading-main{display:flex;align-items:center;gap:13px;min-width:0}
.inspection-heading-icon{width:48px;height:48px;flex:0 0 48px;display:grid;place-items:center;border-radius:15px;background:linear-gradient(145deg,#e8f0ff,#f2f6ff);color:#28599b;font-size:1.3rem}
.inspection-eyebrow{display:block;margin-bottom:3px;color:#7c8ca4;font-size:.64rem;font-weight:850;letter-spacing:.13em}
.inspection-heading h2{margin:0;color:#1e3557;font-size:1.18rem;font-weight:800;line-height:1.3}
.inspection-officer{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:6px;color:#72819a;font-size:.78rem}
.inspection-officer i{color:#3e70ae}.inspection-officer strong{color:#31537f;font-weight:750;overflow-wrap:anywhere}
.inspection-time-chip{display:inline-flex;align-items:center;gap:7px;padding:8px 12px;border:1px solid #dbe6f5;border-radius:999px;background:#f4f8fe;color:#315b92;font-size:.8rem;font-weight:800;white-space:nowrap}
.inspection-result-table thead th{background:#f0f5fc;color:#28466f;padding:12px 14px}
.inspection-result-table tbody td{padding:12px 14px}
.inspection-result-table th:nth-child(2),.inspection-result-table th:nth-child(3),.inspection-result-table td:nth-child(2),.inspection-result-table td:nth-child(3){width:150px}
.inspection-result-table td:first-child{min-width:220px}
.status-result-badge{display:inline-flex;align-items:center;min-width:75px;justify-content:center;padding:7px 11px!important}
.inspection-photo-button{border-radius:10px;padding:.42rem .72rem;font-weight:700}
.inspection-mobile-list{margin-top:0}.inspection-mobile-item{padding:14px;margin-bottom:9px;border-radius:14px}
.inspection-mobile-meta{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:9px}
.inspection-mobile-meta>div{display:flex;flex-direction:column;align-items:flex-start;gap:5px;padding:10px;border:1px solid #edf1f6;border-radius:11px;background:#f8faff;min-width:0}
.inspection-mobile-meta small{color:#8290a5;font-size:.69rem;font-weight:700}
@media(max-width:767.98px){.inspection-section{padding:12px;border-radius:16px}.inspection-heading{align-items:flex-start}.inspection-heading-icon{width:42px;height:42px;flex-basis:42px;border-radius:13px}.inspection-heading h2{font-size:1.02rem}.inspection-time-chip{padding:7px 9px;font-size:.73rem}.inspection-officer{font-size:.74rem}}
</style>
<style>
.verification-card{border:1px solid #e2e9f3;border-radius:18px;background:linear-gradient(145deg,#f8faff,#fff 68%);padding:18px;margin-top:14px;box-shadow:0 7px 20px rgba(23,49,86,.045)}
.verification-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.verification-role{font-weight:700;color:#0b2e6d}
.verification-state{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 11px;font-size:.78rem;font-weight:700}
.verification-state.done{background:#e8f6ee;color:#167343}.verification-state.pending{background:#fff4df;color:#98620b}
.verification-name{font-size:.86rem;color:#697386;margin-top:6px}.user-name-display{display:inline-flex;align-items:center;gap:9px;padding:9px 13px;border-radius:13px;background:linear-gradient(135deg,#edf3fb,#f8faff);border:1px solid rgba(11,46,109,.10);color:#183b70;font-weight:700;max-width:100%;overflow-wrap:anywhere}.user-name-display i{color:#5578a9}
.verification-form{border-top:1px solid #e5ebf3;margin-top:17px;padding-top:17px;display:flex;flex-direction:column;align-items:flex-start;gap:12px}.verification-form .form-label{margin:0!important}.verification-form .user-name-display{margin:0!important}.verification-form .btn{margin-top:2px}@media(min-width:768px){.verification-form{flex-direction:row;align-items:center;gap:16px;flex-wrap:wrap}.verification-form .form-label{width:auto!important;margin-right:2px!important}.verification-form .btn{margin:0!important}}
.verification-note-field{flex:1 1 100%;min-width:0}.verification-note-field textarea{resize:none;min-height:112px;width:100%;border:1px solid #d9e2ef;border-radius:14px;background:#fff;padding:12px 14px;line-height:1.55;color:#263b59;box-shadow:inset 0 1px 2px rgba(20,43,75,.025);transition:border-color .18s,box-shadow .18s}.verification-note-field textarea:focus{border-color:#6d94ca;box-shadow:0 0 0 .22rem rgba(49,95,165,.12);outline:0}.verification-note-field textarea::placeholder{color:#9aa7b8}.verification-note-field .form-label{display:block;margin-bottom:8px!important;color:#344b6c;font-size:.9rem;font-weight:750}.verification-form .btn{border-radius:12px;padding:.65rem 1.1rem;font-weight:700;box-shadow:0 5px 12px rgba(13,75,153,.12)}.verification-note-read{font-size:.9rem;line-height:1.45;overflow-wrap:anywhere}@media(min-width:768px){.verification-form .verification-note-field{flex:1 1 360px;max-width:560px}}
</style>
<div class="card p-3 p-md-4"><h2 class="h5 fw-bold mb-1">Status Verifikasi</h2><p class="small text-secondary mb-3">Progres persetujuan ceklist ruangan.</p>
<div class="verification-card"><div class="verification-head"><span class="verification-role">PJ Sarpras</span><span class="verification-state <?= $room['verif_pj_sarpras']?'done':'pending' ?>"><i class="bi <?= $room['verif_pj_sarpras']?'bi-check-circle-fill':'bi-clock' ?>"></i><?= $room['verif_pj_sarpras']?'Terverifikasi':'Menunggu verifikasi' ?></span></div><?php if($room['nama_verifikator_pj']): ?><div class="verification-name">Oleh <?= e($room['nama_verifikator_pj']) ?><?= $room['waktu_verif_pj']?' · '.e($room['waktu_verif_pj']):'' ?></div><?php endif; ?>
<?php if($room['verif_pj_sarpras']): ?><div class="verification-note-read mt-3"><strong>Catatan PJ Sarpras</strong><div class="mt-1"><?= trim((string)($room['catatan_pj_sarpras']??''))!==''?nl2br(e($room['catatan_pj_sarpras'])):'<span class="text-secondary fst-italic">Tidak ada catatan.</span>' ?></div></div><?php endif; ?>
<?php if(current_role()==='pj_sarpras'&&!$room['verif_pj_sarpras']&&$room['hid']): ?><form class="verification-form" method="post" action="<?= e(base_url('verifikator/proses_verifikasi.php')) ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$room['hid'] ?>"><input type="hidden" name="jenis" value="pj"><input type="hidden" name="tanggal" value="<?= e($date) ?>"><div class="form-label w-100 mb-2">Nama verifikator</div><div class="user-name-display mb-3"><?= e($roleNames['pj_sarpras']) ?></div><div class="verification-note-field w-100"><label class="form-label fw-semibold" for="note_pj">Catatan PJ Sarpras</label><textarea class="form-control" id="note_pj" name="catatan_verifikator" rows="3" maxlength="5000" placeholder="Tambahkan catatan verifikasi (opsional)..."></textarea></div><button class="btn btn-primary">Verifikasi PJ Sarpras</button></form><?php endif; ?></div>
<div class="verification-card"><div class="verification-head"><span class="verification-role">Kasubbag Umum</span><span class="verification-state <?= $room['verif_kasubbag']?'done':'pending' ?>"><i class="bi <?= $room['verif_kasubbag']?'bi-check-circle-fill':'bi-clock' ?>"></i><?= $room['verif_kasubbag']?'Terverifikasi':'Menunggu verifikasi' ?></span></div><?php if($room['nama_verifikator_kasubbag']): ?><div class="verification-name">Oleh <?= e($room['nama_verifikator_kasubbag']) ?><?= $room['waktu_verif_kasubbag']?' · '.e($room['waktu_verif_kasubbag']):'' ?></div><?php endif; ?>
<?php if($room['verif_kasubbag']): ?><div class="verification-note-read mt-3"><strong>Catatan Kasubbag Umum</strong><div class="mt-1"><?= trim((string)($room['catatan_kasubbag_umum']??''))!==''?nl2br(e($room['catatan_kasubbag_umum'])):'<span class="text-secondary fst-italic">Tidak ada catatan.</span>' ?></div></div><?php endif; ?>
<?php if(current_role()==='kasubbag_umum'&&!$room['verif_kasubbag']&&$room['hid']): ?><form class="verification-form" method="post" action="<?= e(base_url('verifikator/proses_verifikasi.php')) ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$room['hid'] ?>"><input type="hidden" name="jenis" value="kasubbag"><input type="hidden" name="tanggal" value="<?= e($date) ?>"><div class="form-label w-100 mb-2">Nama verifikator</div><div class="user-name-display mb-3"><?= e($roleNames['kasubbag_umum']) ?></div><div class="verification-note-field w-100"><label class="form-label fw-semibold" for="note_kasubbag">Catatan Kasubbag Umum</label><textarea class="form-control" id="note_kasubbag" name="catatan_verifikator" rows="3" maxlength="5000" placeholder="Tambahkan catatan verifikasi (opsional)..."></textarea></div><button class="btn btn-primary">Verifikasi Kasubbag Umum</button></form><?php endif; ?></div></div>
<div class="modal fade" id="fotoModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">Foto Bukti</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body text-center"><img id="fotoBesar" class="img-fluid rounded" alt="Foto bukti" style="max-height:70vh;object-fit:contain"><div id="fotoError" class="alert alert-warning mt-3 d-none">Foto tidak dapat dimuat. Periksa file pada folder uploads/bukti.</div></div></div></div></div>
<script>
const fotoModal=document.getElementById('fotoModal');
fotoModal.addEventListener('show.bs.modal',e=>{
 const img=document.getElementById('fotoBesar'),err=document.getElementById('fotoError');
 err.classList.add('d-none');img.classList.remove('d-none');img.onload=()=>err.classList.add('d-none');
 img.onerror=()=>{img.classList.add('d-none');err.classList.remove('d-none');};
 img.src=e.relatedTarget.getAttribute('data-foto')||'';
});
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
