<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['pj_sarpras','kasubbag_umum','admin']);
$rid=(int)($_GET['ruangan']??0);$date=$_GET['tanggal']??date('Y-m-d');
$s=$conn->prepare('SELECT r.*,h.id hid,h.catatan_temuan,h.verif_pj_sarpras,h.waktu_verif_pj,h.nama_verifikator_pj,h.verif_kasubbag,h.waktu_verif_kasubbag,h.nama_verifikator_kasubbag FROM ruangan r LEFT JOIN ceklist_harian h ON h.ruangan_id=r.id AND h.tanggal=? WHERE r.id=?');
$s->bind_param('si',$date,$rid);$s->execute();$room=$s->get_result()->fetch_assoc();if(!$room)redirect('verifikator/dashboard.php');
$roleNames=['pj_sarpras'=>'','kasubbag_umum'=>''];$s=$conn->prepare("SELECT role,nama_lengkap FROM users WHERE role IN ('pj_sarpras','kasubbag_umum')");$s->execute();foreach(stmt_rows($s) as $n)$roleNames[$n['role']]=$n['nama_lengkap'];
$data=[];if($room['hid']){$hid=(int)$room['hid'];$s=$conn->prepare('SELECT d.*,i.nama_indikator,u.nama_lengkap FROM ceklist_detail d JOIN indikator_ruangan i ON i.id=d.indikator_id JOIN users u ON u.id=d.petugas_id WHERE d.ceklist_harian_id=? ORDER BY d.jam_cek,i.urutan');$s->bind_param('i',$hid);$s->execute();foreach(stmt_rows($s) as $x)$data[$x['jam_cek']][]=$x;}
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? ORDER BY urutan');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);
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
$page_title='Detail Ceklist';include __DIR__.'/../includes/header.php';
?>
<a href="<?= e(base_url('verifikator/dashboard.php?tanggal='.$date)) ?>" class="btn btn-light btn-back-rekap"><i class="bi bi-arrow-left me-2"></i>Kembali</a>
<?php if(($_GET['verifikasi']??'')==='success'): ?><div class="alert alert-success border-0 rounded-4 mt-3"><i class="bi bi-check-circle-fill me-2"></i><?= ($_GET['jenis']??'')==='pj'?'Ceklist berhasil diverifikasi oleh PJ Sarpras.':'Ceklist berhasil diverifikasi oleh Kasubbag Umum.' ?></div><?php endif; ?>
<h1 class="h3 page-heading mt-3"><?= e($room['nama_ruangan']) ?></h1><p class="muted"><?= e(tanggal_indo($date)) ?></p>
<style>
.btn-back-rekap{display:inline-flex;align-items:center;border:1px solid rgba(11,46,109,.10);background:rgba(255,255,255,.78);color:#53627a;border-radius:14px;padding:.62rem 1rem;font-weight:600;box-shadow:0 5px 16px rgba(11,46,109,.055);transition:background .18s,transform .18s}.btn-back-rekap:hover{background:#edf1f7;color:#263c5d;transform:translateX(-2px)}.detail-table-wrap{overflow:hidden;border-radius:16px}.detail-table{margin:0}.detail-table th,.detail-table td{vertical-align:middle;padding:.8rem .7rem}.detail-table td:first-child{font-weight:600;color:#34334c;min-width:190px;white-space:normal;overflow-wrap:break-word;word-break:normal}.detail-mobile-list{display:none}.detail-mobile-item{background:rgba(255,255,255,.72);border:1px solid rgba(255,255,255,.95);border-radius:17px;padding:14px;margin-bottom:10px;min-width:0}.detail-mobile-title{font-weight:650;color:#34334c;line-height:1.4;overflow-wrap:break-word;word-break:normal;margin-bottom:10px}.detail-mobile-meta{display:grid;grid-template-columns:1fr 1fr;gap:10px}.detail-mobile-meta small{display:block;color:#85879a;font-size:.7rem;margin-bottom:4px}.detail-mobile-meta strong{font-size:.83rem;overflow-wrap:anywhere}
@media(max-width:767.98px){.detail-desktop-table{display:none}.detail-mobile-list{display:block}.detail-mobile-meta{grid-template-columns:minmax(0,1fr) minmax(0,1fr)}.detail-mobile-item .btn{padding:.45rem .7rem}}
</style>
<?php foreach(['08:00','11:00','14:00'] as $jam): ?><div class="card p-3 p-md-4 mb-3"><h2 class="h5 fw-bold mb-3">Pengecekan <?= e($jam) ?></h2><?php $map=[];foreach($data[$jam]??[] as $d)$map[(int)$d['indikator_id']]=$d; ?>
<div class="detail-desktop-table"><div class="detail-table-wrap"><table class="table table-sm align-middle detail-table"><thead><tr><th>Indikator</th><th>Status</th><th>Petugas</th><th>Foto</th></tr></thead><tbody>
<?php foreach($items as $it):$d=$map[(int)$it['id']]??null; ?><tr><td><?= e($it['nama_indikator']) ?></td><td><?php if($d): ?><span class="badge <?= $d['status']==='Bersih/Rapi'?'text-bg-success':'text-bg-danger' ?>"><?= $d['status']==='Bersih/Rapi'?'Ya':'Tidak' ?></span><?php else: ?>-<?php endif; ?></td><td><?= e($d['nama_petugas']??$d['nama_lengkap']??'-') ?></td><td><?php if($d&&$d['foto_bukti']): ?><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= e(url_foto_bukti($d['foto_bukti'])) ?>">Lihat</button><?php else: ?>-<?php endif; ?></td></tr><?php endforeach; ?>
</tbody></table></div></div>
<div class="detail-mobile-list"><?php foreach($items as $it):$d=$map[(int)$it['id']]??null; ?><div class="detail-mobile-item"><div class="detail-mobile-title"><?= e($it['urutan'].'. '.$it['nama_indikator']) ?></div><div class="detail-mobile-meta"><div><small>Status</small><?php if($d): ?><span class="badge <?= $d['status']==='Bersih/Rapi'?'text-bg-success':'text-bg-danger' ?>"><?= $d['status']==='Bersih/Rapi'?'Ya':'Tidak' ?></span><?php else: ?><strong>-</strong><?php endif; ?></div><div><small>Petugas</small><strong><?= e($d['nama_petugas']??$d['nama_lengkap']??'-') ?></strong></div><div><small>Foto Bukti</small><?php if($d&&$d['foto_bukti']): ?><button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#fotoModal" data-foto="<?= e(url_foto_bukti($d['foto_bukti'])) ?>">Lihat Foto</button><?php else: ?><strong>-</strong><?php endif; ?></div></div></div><?php endforeach; ?></div></div><?php endforeach; ?>
<div class="card p-3 mb-3"><h2 class="h6 fw-bold mb-2">Catatan</h2><?= trim((string)($room['catatan_temuan']??''))!=='' ? nl2br(e($room['catatan_temuan'])) : '<span class="text-secondary fst-italic">Tidak ada catatan.</span>' ?></div>
<style>
.verification-card{border:1px solid rgba(11,46,109,.10);border-radius:16px;background:linear-gradient(135deg,rgba(11,46,109,.045),rgba(255,255,255,.96));padding:16px;margin-top:12px}
.verification-head{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.verification-role{font-weight:700;color:#0b2e6d}
.verification-state{display:inline-flex;align-items:center;gap:6px;border-radius:999px;padding:6px 11px;font-size:.78rem;font-weight:700}
.verification-state.done{background:#e8f6ee;color:#167343}.verification-state.pending{background:#fff4df;color:#98620b}
.verification-name{font-size:.86rem;color:#697386;margin-top:6px}.user-name-display{display:inline-flex;align-items:center;gap:9px;padding:9px 13px;border-radius:13px;background:linear-gradient(135deg,#edf3fb,#f8faff);border:1px solid rgba(11,46,109,.10);color:#183b70;font-weight:700;max-width:100%;overflow-wrap:anywhere}.user-name-display i{color:#5578a9}
.verification-form{border-top:1px solid rgba(11,46,109,.10);margin-top:14px;padding-top:14px;display:flex;flex-direction:column;align-items:flex-start;gap:10px}.verification-form .form-label{margin:0!important}.verification-form .user-name-display{margin:0!important}.verification-form .btn{margin-top:2px}@media(min-width:768px){.verification-form{flex-direction:row;align-items:center;gap:16px;flex-wrap:wrap}.verification-form .form-label{width:auto!important;margin-right:2px!important}.verification-form .btn{margin:0!important}}
</style>
<div class="card p-3 p-md-4"><h2 class="h5 fw-bold mb-1">Status Verifikasi</h2><p class="small text-secondary mb-3">Progres persetujuan ceklist ruangan.</p>
<div class="verification-card"><div class="verification-head"><span class="verification-role">PJ Sarpras</span><span class="verification-state <?= $room['verif_pj_sarpras']?'done':'pending' ?>"><i class="bi <?= $room['verif_pj_sarpras']?'bi-check-circle-fill':'bi-clock' ?>"></i><?= $room['verif_pj_sarpras']?'Terverifikasi':'Menunggu verifikasi' ?></span></div><?php if($room['nama_verifikator_pj']): ?><div class="verification-name">Oleh <?= e($room['nama_verifikator_pj']) ?><?= $room['waktu_verif_pj']?' · '.e($room['waktu_verif_pj']):'' ?></div><?php endif; ?>
<?php if(current_role()==='pj_sarpras'&&!$room['verif_pj_sarpras']&&$room['hid']): ?><form class="verification-form" method="post" action="<?= e(base_url('verifikator/proses_verifikasi.php')) ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$room['hid'] ?>"><input type="hidden" name="jenis" value="pj"><input type="hidden" name="tanggal" value="<?= e($date) ?>"><div class="form-label w-100 mb-2">Nama verifikator</div><div class="user-name-display mb-3"><?= e($roleNames['pj_sarpras']) ?></div><button class="btn btn-primary">Verifikasi PJ Sarpras</button></form><?php endif; ?></div>
<div class="verification-card"><div class="verification-head"><span class="verification-role">Kasubbag Umum</span><span class="verification-state <?= $room['verif_kasubbag']?'done':'pending' ?>"><i class="bi <?= $room['verif_kasubbag']?'bi-check-circle-fill':'bi-clock' ?>"></i><?= $room['verif_kasubbag']?'Terverifikasi':'Menunggu verifikasi' ?></span></div><?php if($room['nama_verifikator_kasubbag']): ?><div class="verification-name">Oleh <?= e($room['nama_verifikator_kasubbag']) ?><?= $room['waktu_verif_kasubbag']?' · '.e($room['waktu_verif_kasubbag']):'' ?></div><?php endif; ?>
<?php if(current_role()==='kasubbag_umum'&&!$room['verif_kasubbag']&&$room['hid']): ?><form class="verification-form" method="post" action="<?= e(base_url('verifikator/proses_verifikasi.php')) ?>"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$room['hid'] ?>"><input type="hidden" name="jenis" value="kasubbag"><input type="hidden" name="tanggal" value="<?= e($date) ?>"><div class="form-label w-100 mb-2">Nama verifikator</div><div class="user-name-display mb-3"><?= e($roleNames['kasubbag_umum']) ?></div><button class="btn btn-primary">Verifikasi Kasubbag Umum</button></form><?php endif; ?></div></div>
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
