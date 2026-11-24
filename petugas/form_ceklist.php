<?php
require_once __DIR__.'/../config/koneksi.php'; require_role(['petugas']);
$qr=trim($_GET['qr']??''); $rid=(int)($_GET['ruangan']??0);
if($qr!==''){ $s=$conn->prepare('SELECT * FROM ruangan WHERE kode_qr=?'); $s->bind_param('s',$qr); } else { $s=$conn->prepare('SELECT * FROM ruangan WHERE id=?'); $s->bind_param('i',$rid); }
$s->execute(); $room=$s->get_result()->fetch_assoc(); if(!$room)redirect('petugas/index.php');
$rid=(int)$room['id']; $roomTitle=preg_replace('/\s*\([^)]*\)\s*$/u','',(string)$room['nama_ruangan']); $tanggal=date('Y-m-d'); $isVehicle=strtolower($room['kode_qr'])==='kendaraan-01' || str_contains(mb_strtolower($room['nama_ruangan']),'kendaraan');
$s=$conn->prepare('SELECT id,nama_indikator,urutan FROM indikator_ruangan WHERE ruangan_id=? AND is_active=1 ORDER BY urutan,id');$s->bind_param('i',$rid);$s->execute();$indikator=stmt_rows($s);
$s=$conn->prepare('SELECT id FROM ceklist_harian WHERE ruangan_id=? AND tanggal=?');$s->bind_param('is',$rid,$tanggal);$s->execute();$harian=$s->get_result()->fetch_assoc();$hid=(int)($harian['id']??0);
$vehicles=[]; if($isVehicle){$vehicles=$conn->query('SELECT id,nama FROM kendaraan ORDER BY id')->fetch_all(MYSQLI_ASSOC);}
$vehicleId=$isVehicle?(int)($_GET['kendaraan']??0):0; $vehicleValid=!$isVehicle || in_array($vehicleId,array_column($vehicles,'id'));
$plate='';if($isVehicle && $vehicleValid && $vehicleId && $hid){$s=$conn->prepare('SELECT no_polisi FROM ceklist_harian_kendaraan WHERE ceklist_harian_id=? AND kendaraan_id=?');$s->bind_param('ii',$hid,$vehicleId);$s->execute();$plate=(string)($s->get_result()->fetch_assoc()['no_polisi']??'');}
$done=[];$existing=[];$jam='';$times=[];$ts=$conn->prepare('SELECT jam_cek FROM jadwal_jam_ruangan WHERE ruangan_id=? ORDER BY urutan');$ts->bind_param('i',$rid);$ts->execute();foreach(stmt_rows($ts) as $t)$times[]=$t['jam_cek'];if(!$times)$times=['08:00','11:00','14:00'];
if(!$isVehicle || ($vehicleId && $vehicleValid)){$s=$conn->prepare('SELECT DISTINCT jam_cek FROM ceklist_detail WHERE ceklist_harian_id=? AND kendaraan_id=?');$s->bind_param('ii',$hid,$vehicleId);$s->execute();foreach(stmt_rows($s) as $x)$done[]=$x['jam_cek'];$jam=$_GET['jam']??'';if(!in_array($jam,$times,true))$jam='';if($hid && $jam!==''){$s=$conn->prepare('SELECT indikator_id,status,foto_bukti,nama_petugas FROM ceklist_detail WHERE ceklist_harian_id=? AND kendaraan_id=? AND jam_cek=?');$s->bind_param('iis',$hid,$vehicleId,$jam);$s->execute();foreach(stmt_rows($s) as $x)$existing[(int)$x['indikator_id']]=$x;}}
$isDone=$jam!=='' && in_array($jam,$done,true);$namaPetugasAktif=(string)($_SESSION['user']['nama_lengkap']??'');$page_title='Form Ceklist';include __DIR__.'/../includes/header.php';
?>
<div class="mb-3"><a href="<?= e(base_url('petugas/index.php')) ?>" class="btn btn-light btn-back-rekap"><i class="bi bi-arrow-left me-2"></i>Kembali</a></div>
<?php if(isset($_GET['saved'])): ?><div class="alert alert-success border-0 rounded-4 shadow-sm save-success-alert" role="status"><i class="bi bi-check-circle-fill me-2"></i>Ceklist jam <?= e($_GET['jam']??'') ?> berhasil <?= isset($_GET['updated'])?'diperbarui':'disimpan' ?>.</div><?php endif; ?><h1 class="h3 page-heading"><?= e($roomTitle) ?></h1><p class="muted"><?= e(tanggal_indo($tanggal)) ?> · Silakan pilih jam pengecekan</p>
<?php if($isVehicle && !$vehicleId): ?><div class="row g-3 mb-3"><?php foreach($vehicles as $v): $s=$conn->prepare('SELECT no_polisi FROM ceklist_harian_kendaraan WHERE ceklist_harian_id=? AND kendaraan_id=?');$s->bind_param('ii',$hid,$v['id']);$s->execute();$vplate=(string)($s->get_result()->fetch_assoc()['no_polisi']??'');$s=$conn->prepare('SELECT COUNT(*) total FROM ceklist_detail WHERE ceklist_harian_id=? AND kendaraan_id=?');$s->bind_param('ii',$hid,$v['id']);$s->execute();$vdone=(int)$s->get_result()->fetch_assoc()['total']>0; ?><div class="col-12 col-md-4"><div class="card p-3 h-100"><div class="room-icon mb-2"><i class="bi bi-car-front"></i></div><h2 class="h5"><?= e($v['nama']) ?></h2><?php if($vplate): ?><div class="text-secondary small mb-2"><?= e($vplate) ?></div><?php endif; ?><div class="small mb-3"><?= $vdone?'<span class="text-success"><i class="bi bi-check-circle-fill"></i> Sudah mulai diperiksa</span>':'<span class="text-muted">Belum diperiksa</span>' ?></div><a class="btn btn-primary mt-auto" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$rid.'&kendaraan='.$v['id'])) ?>">Pilih Kendaraan</a></div></div><?php endforeach; ?></div><p class="text-muted small">Setiap kendaraan dapat diperiksa pada jam yang sama, dengan data, foto, dan nomor polisi masing-masing.</p>
<?php elseif($isVehicle && !$vehicleValid): ?><div class="alert alert-warning">Pilihan kendaraan tidak valid.</div><a class="btn btn-light" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$rid)) ?>">Kembali</a>
<?php else: ?>
<?php if($isVehicle): ?><div class="mb-3"><a class="btn btn-light btn-back-rekap" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$rid)) ?>"><i class="bi bi-arrow-left me-2"></i>Kembali ke kendaraan</a></div><div class="user-name-display mb-3"><i class="bi bi-car-front"></i> <?= e($vehicles[array_search($vehicleId,array_column($vehicles,'id'))]['nama']??'Kendaraan') ?><?php if($plate): ?> · <?= e($plate) ?><?php endif; ?></div><?php endif; ?>
<div class="d-flex gap-2 mb-3 flex-wrap justify-content-center"><?php foreach($times as $j): ?><a class="btn <?= $jam===$j?'btn-primary':'btn-outline-primary' ?> position-relative" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$rid.($isVehicle?'&kendaraan='.$vehicleId:'').'&jam='.urlencode($j))) ?>"><?= e($j) ?> <?php if(in_array($j,$done,true)): ?><i class="bi bi-check-circle-fill ms-1"></i><?php endif; ?></a><?php endforeach; ?></div>
<?php if($jam===''): ?><div class="card p-4 p-md-5 text-center choose-time"><div class="choose-time-icon"><i class="bi bi-clock-history"></i></div><h2 class="h5 fw-bold mt-3">Silakan Pilih Jam</h2><p class="muted mb-0">Pilih salah satu jadwal di atas untuk menampilkan indikator pengecekan.</p></div>
<?php else: ?><?php if(!$indikator): ?><div class="alert alert-warning">Indikator ruangan belum tersedia. Hubungi administrator.</div><?php else: ?>
<form method="post" action="<?= e(base_url('petugas/simpan_ceklist.php')) ?>" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="ruangan_id" value="<?= $rid ?>"><input type="hidden" name="jam_cek" value="<?= e($jam) ?>"><input type="hidden" name="kendaraan_id" value="<?= $vehicleId ?>">
<div class="card p-3 p-md-4 mb-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0 fw-bold">Indikator Pengecekan</h2><span class="badge" style="background:#e8e6ff;color:#5b52e8"><?= e($jam) ?></span></div>
<?php foreach($indikator as $i):$iid=(int)$i['id'];$old=$existing[$iid]??[];$needsAction=($old['status']??'')==='Tidak'; ?><div class="indicator-card mb-3" data-indicator="<?= $iid ?>"><div class="fw-semibold mb-3"><?= e($i['urutan'].'. '.preg_replace('/\s*\/\s*/u',' & ',$i['nama_indikator'])) ?></div><div class="choice mb-3"><label><input type="radio" name="status[<?= $iid ?>]" value="Ya" required <?= ($old['status']??'')==='Ya'?'checked':'' ?>><span><i class="bi bi-check-circle me-1"></i>Ya</span></label><label><input type="radio" name="status[<?= $iid ?>]" value="Tidak" required <?= $needsAction?'checked':'' ?>><span><i class="bi bi-exclamation-circle me-1"></i>Tidak</span></label></div>
<div class="photo-guidance <?= $needsAction?'':'d-none' ?>" id="photo_notice_<?= $iid ?>" role="status"><i class="bi bi-info-circle-fill me-2"></i>Indikator perlu tindakan. Silakan unggah foto sebagai bukti kondisi.</div><div class="photo-upload <?= $needsAction?'':'d-none' ?>" id="photo_wrap_<?= $iid ?>"><div class="row g-2 align-items-center"><div class="col-12 col-md-7"><label class="form-label fw-semibold mb-1" for="foto_<?= $iid ?>"><i class="bi bi-camera me-1"></i>Foto bukti indikator ini</label><input class="form-control foto-indikator" type="file" id="foto_<?= $iid ?>" name="foto_bukti[<?= $iid ?>]" accept="image/*" capture="environment" <?= ($needsAction&&empty($old['foto_bukti']))?'required':'' ?>><div class="form-text">Foto akan dikompres otomatis. Ukuran asli maksimal 10 MB.</div></div><div class="col-12 col-md-5"><?php if(!empty($old['foto_bukti'])): ?><div class="small text-success mb-1"><i class="bi bi-check-circle-fill"></i> Foto tersimpan</div><img class="photo-preview" src="<?= e(base_url($old['foto_bukti'])) ?>" alt="Foto bukti sebelumnya" id="preview_<?= $iid ?>" data-has-existing="1"><?php else: ?><img class="photo-preview d-none" alt="Pratinjau foto" id="preview_<?= $iid ?>" data-has-existing="0"><?php endif; ?></div></div></div></div><?php endforeach; ?>
<div class="mb-3"><div class="form-label fw-semibold">Nama Petugas Kebersihan</div><div class="user-name-display"><i class="bi bi-person-badge"></i><span><?= e($namaPetugasAktif) ?></span></div></div><?php if($isVehicle): ?><div class="mb-3"><label class="form-label fw-semibold">No. Polisi Kendaraan</label><input class="form-control" name="no_polisi" maxlength="40" placeholder="Masukkan nomor polisi kendaraan" value="<?= e($plate) ?>"></div><?php endif; ?><button class="btn btn-primary btn-lg w-100"><?= $isDone?'Simpan Perubahan':'Simpan Ceklist' ?> <?= e($jam) ?></button></div></form><?php endif; ?>

<?php endif; ?><?php endif; ?>
<style>.save-success-alert{padding:15px 18px;font-weight:600;background:linear-gradient(120deg,#e8f8ef,#f5fff8);color:#176b3a;border:1px solid #ccebd8!important}.btn-back-rekap{display:inline-flex;align-items:center;border:1px solid rgba(11,46,109,.10);background:rgba(255,255,255,.78);color:#53627a;border-radius:14px;padding:.62rem 1rem;font-weight:600;box-shadow:0 5px 16px rgba(11,46,109,.055);transition:background .18s,transform .18s}.btn-back-rekap:hover{background:#edf1f7;color:#263c5d;transform:translateX(-2px)}.user-name-display{display:inline-flex;align-items:center;gap:9px;padding:9px 13px;border-radius:13px;background:linear-gradient(135deg,#edf3fb,#f8faff);border:1px solid rgba(11,46,109,.10);color:#183b70;font-weight:700;max-width:100%;overflow-wrap:anywhere}.user-name-display i{color:#5578a9}.choose-time{min-height:220px;justify-content:center;align-items:center}.choose-time-icon{width:64px;height:64px;border-radius:22px;display:flex;align-items:center;justify-content:center;background:#eeebff;color:#6558d9;font-size:1.8rem}.photo-guidance{padding:11px 14px;margin-bottom:12px;border-radius:13px;background:#eef3ff;color:#0B2E6D;font-size:.88rem;font-weight:500}.photo-upload{padding:13px;border-radius:16px;background:rgba(238,243,255,.58);border:1px solid rgba(11,46,109,.09)}</style>
<script>
document.querySelectorAll('.indicator-card').forEach(card=>{
 const id=card.dataset.indicator,radios=card.querySelectorAll('input[name="status['+id+']"]'),wrap=document.getElementById('photo_wrap_'+id),notice=document.getElementById('photo_notice_'+id),file=document.getElementById('foto_'+id),preview=document.getElementById('preview_'+id);
 function updatePhoto(){const selected=card.querySelector('input[name="status['+id+']"]:checked'),need=selected&&selected.value==='Tidak';wrap.classList.toggle('d-none',!need);notice.classList.toggle('d-none',!need);
  if(file){if(!need&&file.files.length){file.value='';if(preview&&preview.dataset.hasExisting!=='1'){preview.src='';preview.classList.add('d-none')}}const hasOldPhoto=preview?.dataset.hasExisting==='1';file.required=!!need&&!hasOldPhoto&&!file.files.length;}
 }
 radios.forEach(r=>r.addEventListener('change',updatePhoto));updatePhoto();
});
document.querySelectorAll('.foto-indikator').forEach(input=>input.addEventListener('change',()=>{const file=input.files[0],img=document.getElementById('preview_'+input.id.replace('foto_',''));if(file&&img){img.src=URL.createObjectURL(file);img.classList.remove('d-none')}}));

// Kompres foto di browser sebelum dikirim agar upload dari kamera HP lebih ringan.
const checklistForm = document.querySelector('form[action*="simpan_ceklist.php"]');
const MAX_ORIGINAL_SIZE = 10 * 1024 * 1024;
const MAX_DIMENSION = 1600;


function loadImageForCompression(file) {
  return new Promise((resolve, reject) => {
    const url = URL.createObjectURL(file);
    const image = new Image();
    image.onload = () => { URL.revokeObjectURL(url); resolve(image); };
    image.onerror = () => { URL.revokeObjectURL(url); reject(new Error('Foto tidak dapat dibaca oleh browser.')); };
    image.src = url;
  });
}


function canvasToJpeg(canvas, quality) {
  return new Promise((resolve, reject) => {
    canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Gagal memproses foto.')), 'image/jpeg', quality);
  });
}


async function compressPhoto(file) {
  if (!file.type.startsWith('image/')) throw new Error('File yang dipilih harus berupa gambar.');
  if (file.size > MAX_ORIGINAL_SIZE) throw new Error('Ukuran foto asli maksimal 10 MB.');
  // Foto kecil yang sudah JPEG tidak perlu diproses ulang.
  if (file.type === 'image/jpeg' && file.size <= 700 * 1024) return file;

  const image = await loadImageForCompression(file);
  const scale = Math.min(1, MAX_DIMENSION / Math.max(image.naturalWidth, image.naturalHeight));
  const canvas = document.createElement('canvas');
  canvas.width = Math.max(1, Math.round(image.naturalWidth * scale));
  canvas.height = Math.max(1, Math.round(image.naturalHeight * scale));
  const context = canvas.getContext('2d', { alpha: false });
  if (!context) throw new Error('Browser tidak mendukung kompresi foto.');
  context.fillStyle = '#ffffff';
  context.fillRect(0, 0, canvas.width, canvas.height);
  context.drawImage(image, 0, 0, canvas.width, canvas.height);

  let blob = await canvasToJpeg(canvas, 0.80);
  if (blob.size > 900 * 1024) blob = await canvasToJpeg(canvas, 0.70);
  if (blob.size > 900 * 1024) blob = await canvasToJpeg(canvas, 0.60);
  const safeName = (file.name || 'foto-bukti').replace(/\.[^.]+$/, '') + '.jpg';
  return new File([blob], safeName, { type: 'image/jpeg', lastModified: Date.now() });
}

if (checklistForm) {
  checklistForm.addEventListener('submit', async event => {
    const photoInputs = [...checklistForm.querySelectorAll('.foto-indikator')].filter(input => input.files && input.files.length && document.querySelector('input[name="status['+input.id.replace('foto_','')+']"]:checked')?.value === 'Tidak');
    if (!photoInputs.length) return;
    event.preventDefault();
    const submitButton = checklistForm.querySelector('button[type="submit"], button:not([type])');
    const originalButtonText = submitButton ? submitButton.innerHTML : '';
    if (submitButton) { submitButton.disabled = true; submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Mengompres foto...'; }
    try {
      for (const input of photoInputs) {
        const compressed = await compressPhoto(input.files[0]);
        const transfer = new DataTransfer();
        transfer.items.add(compressed);
        input.files = transfer.files;
      }
      if (submitButton) submitButton.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menyimpan ceklist...';
      checklistForm.submit();
    } catch (error) {
      alert(error.message || 'Terjadi kesalahan saat mengompres foto.');
      if (submitButton) { submitButton.disabled = false; submitButton.innerHTML = originalButtonText; }
    }
  });
}
</script>
<?php include __DIR__.'/../includes/footer.php'; ?>
