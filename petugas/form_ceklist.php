<?php
require_once __DIR__.'/../config/koneksi.php'; require_role(['petugas']);
$qr=trim($_GET['qr']??''); $rid=(int)($_GET['ruangan']??0);
if($qr!==''){ $s=$conn->prepare('SELECT * FROM ruangan WHERE kode_qr=?'); $s->bind_param('s',$qr); }
else { $s=$conn->prepare('SELECT * FROM ruangan WHERE id=?'); $s->bind_param('i',$rid); }
$s->execute(); $room=$s->get_result()->fetch_assoc(); if(!$room)redirect('petugas/index.php');
$rid=(int)$room['id']; $tanggal=date('Y-m-d');
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? ORDER BY urutan,id'); $s->bind_param('i',$rid); $s->execute(); $indikator=stmt_rows($s);
$s=$conn->prepare('SELECT id,catatan_temuan FROM ceklist_harian WHERE ruangan_id=? AND tanggal=?'); $s->bind_param('is',$rid,$tanggal); $s->execute(); $harian=$s->get_result()->fetch_assoc();
$done=[]; $s=$conn->prepare('SELECT DISTINCT jam_cek FROM ceklist_detail d JOIN ceklist_harian h ON h.id=d.ceklist_harian_id WHERE h.ruangan_id=? AND h.tanggal=?'); $s->bind_param('is',$rid,$tanggal); $s->execute(); foreach(stmt_rows($s) as $x)$done[]=$x['jam_cek'];
$jam=$_GET['jam']??''; if(!in_array($jam,['08:00','11:00','14:00'],true))$jam='';
$existing=[]; $namaTersimpan=''; if($harian){$hid=(int)$harian['id'];$s=$conn->prepare('SELECT indikator_id,status,foto_bukti,nama_petugas FROM ceklist_detail WHERE ceklist_harian_id=? AND jam_cek=?');$s->bind_param('is',$hid,$jam);$s->execute();foreach(stmt_rows($s) as $x){$existing[(int)$x['indikator_id']]=$x;if(!$namaTersimpan)$namaTersimpan=$x['nama_petugas'];}}
$isDone=$jam!=='' && in_array($jam,$done,true); $page_title='Form Ceklist'; include __DIR__.'/../includes/header.php';
?>
\n<div class="mb-3"><a href="<?= e(base_url('petugas/index.php')) ?>" class="text-decoration-none"><i class="bi bi-arrow-left"></i> Daftar ruangan</a></div>
<h1 class="h3 page-heading"><?= e($room['nama_ruangan']) ?></h1><p class="muted"><?= e(tanggal_indo($tanggal)) ?> · Silakan pilih jam pengecekan</p>
<div class="d-flex gap-2 mb-3 flex-wrap justify-content-center"><?php foreach(['08:00','11:00','14:00'] as $j): ?><a class="btn <?= $jam===$j?'btn-primary':'btn-outline-primary' ?> position-relative" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$rid.'&jam='.urlencode($j))) ?>"><?= e($j) ?> <?php if(in_array($j,$done,true)): ?><i class="bi bi-check-circle-fill ms-1"></i><?php endif; ?></a><?php endforeach; ?></div>
<?php if($jam===''): ?>
<div class="card p-4 p-md-5 text-center choose-time"><div class="choose-time-icon"><i class="bi bi-clock-history"></i></div><h2 class="h5 fw-bold mt-3">Silakan Pilih Jam</h2><p class="muted mb-0">Pilih salah satu jadwal di atas untuk menampilkan indikator pengecekan.</p></div>
<?php else: ?>
<?php if(isset($_GET['saved'])): ?><div class="alert alert-success border-0 rounded-4"><i class="bi bi-check-circle-fill me-2"></i>Ceklist jam <?= e($jam) ?> berhasil disimpan.</div><?php endif; ?>
<?php if(!$indikator): ?><div class="alert alert-warning">Indikator ruangan belum tersedia. Hubungi administrator.</div><?php else: ?>
<form method="post" action="<?= e(base_url('petugas/simpan_ceklist.php')) ?>" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="ruangan_id" value="<?= $rid ?>"><input type="hidden" name="jam_cek" value="<?= e($jam) ?>">
<div class="card p-3 p-md-4 mb-3"><div class="d-flex justify-content-between align-items-center mb-3"><h2 class="h5 mb-0 fw-bold">Indikator Pengecekan</h2><span class="badge" style="background:#e8e6ff;color:#5b52e8"><?= e($jam) ?></span></div>
<?php foreach($indikator as $i): $iid=(int)$i['id']; $old=$existing[$iid]??[]; $needsAction=($old['status']??'')==='Kotor/Perlu Tindakan'; ?><div class="indicator-card mb-3" data-indicator="<?= $iid ?>"><div class="fw-semibold mb-3"><?= e($i['urutan'].'. '.preg_replace('/\s*\/\s*/u', ' & ', $i['nama_indikator'])) ?></div><div class="choice mb-3">
<label><input type="radio" name="status[<?= $iid ?>]" value="Bersih/Rapi" required <?= ($old['status']??'')==='Bersih/Rapi'?'checked':'' ?>><span><i class="bi bi-check-circle me-1"></i>Ya</span></label>
<label><input type="radio" name="status[<?= $iid ?>]" value="Kotor/Perlu Tindakan" required <?= $needsAction?'checked':'' ?>><span><i class="bi bi-exclamation-circle me-1"></i>Tidak</span></label></div>
<div class="photo-guidance <?= $needsAction?'':'d-none' ?>" id="photo_notice_<?= $iid ?>" role="status"><i class="bi bi-info-circle-fill me-2"></i>Indikator perlu tindakan. Silakan unggah foto sebagai bukti kondisi.</div>
<div class="photo-upload <?= $needsAction?'':'d-none' ?>" id="photo_wrap_<?= $iid ?>"><div class="row g-2 align-items-center"><div class="col-12 col-md-7"><label class="form-label fw-semibold mb-1" for="foto_<?= $iid ?>"><i class="bi bi-camera me-1"></i>Foto bukti indikator ini</label><input class="form-control foto-indikator" type="file" id="foto_<?= $iid ?>" name="foto_bukti[<?= $iid ?>]" accept="image/*" capture="environment" <?= ($needsAction && empty($old['foto_bukti']))?'required':'' ?>><div class="form-text">Ambil foto langsung menggunakan kamera atau pilih dari galeri. Foto akan dikompres otomatis sebelum dikirim. Ukuran foto asli maksimal 10 MB.</div></div><div class="col-12 col-md-5"><?php if(!empty($old['foto_bukti'])): ?><div class="small text-success mb-1"><i class="bi bi-check-circle-fill"></i> Foto tersimpan (dipertahankan jika tidak diganti)</div><img class="photo-preview" src="<?= e(base_url($old['foto_bukti'])) ?>" alt="Foto bukti sebelumnya" id="preview_<?= $iid ?>" data-has-existing="1"><?php else: ?><img class="photo-preview d-none" alt="Pratinjau foto" id="preview_<?= $iid ?>" data-has-existing="0"><?php endif; ?></div></div></div></div><?php endforeach; ?>
<div class="mb-3"><label class="form-label fw-semibold" for="nama_petugas">Nama Petugas Kebersihan</label><input class="form-control" id="nama_petugas" name="nama_petugas" maxlength="120" required value="<?= e($namaTersimpan) ?>" placeholder="Masukkan nama petugas yang melakukan pengecekan"></div>
<div class="mb-3"><label class="form-label fw-semibold">Catatan</label><textarea class="form-control" name="catatan_temuan" rows="3" placeholder="Tuliskan temuan atau tindakan yang diperlukan..."><?= e($harian['catatan_temuan']??'') ?></textarea></div>
<button class="btn btn-primary btn-lg w-100"><?= $isDone?'Simpan Perubahan':'Simpan Ceklist' ?> <?= e($jam) ?></button>
</div></form><?php endif; ?>
<?php if($isDone): ?><div class="modal fade" id="alreadyFilledModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 rounded-4" style="background:rgba(255,255,255,.96)"><div class="modal-body text-center p-4"><div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width:58px;height:58px;background:#eeecff;color:#6258ee;font-size:1.5rem"><i class="bi bi-info-circle-fill"></i></div><h5 class="fw-bold">Ceklist <?= e($jam) ?> sudah terisi</h5><p class="text-secondary mb-4">Data sebelumnya, termasuk status, foto, nama petugas, dan catatan tetap tersedia. Pilih Edit jika ingin mengubahnya.</p><div class="d-flex gap-2"><button type="button" class="btn btn-primary flex-fill" data-bs-dismiss="modal">Edit Ceklist</button><a class="btn btn-outline-secondary flex-fill" href="<?= e(base_url('petugas/index.php')) ?>">Oke</a></div></div></div></div></div><script>document.addEventListener('DOMContentLoaded',()=>new bootstrap.Modal(document.getElementById('alreadyFilledModal')).show());</script><?php endif; ?>
<?php endif; ?>
<style>.choose-time{min-height:220px;justify-content:center;align-items:center}.choose-time-icon{width:64px;height:64px;border-radius:22px;display:flex;align-items:center;justify-content:center;background:#eeebff;color:#6558d9;font-size:1.8rem}.photo-guidance{padding:11px 14px;margin-bottom:12px;border-radius:13px;background:#eef3ff;color:#0B2E6D;font-size:.88rem;font-weight:500}.photo-upload{padding:13px;border-radius:16px;background:rgba(238,243,255,.58);border:1px solid rgba(11,46,109,.09)}</style>
<script>
document.querySelectorAll('.indicator-card').forEach(card=>{
 const id=card.dataset.indicator, radios=card.querySelectorAll('input[name="status['+id+']"]'), wrap=document.getElementById('photo_wrap_'+id), notice=document.getElementById('photo_notice_'+id), file=document.getElementById('foto_'+id);
 function updatePhoto(){const selected=card.querySelector('input[name="status['+id+']"]:checked');const need=selected&&selected.value==='Kotor/Perlu Tindakan';wrap.classList.toggle('d-none',!need);notice.classList.toggle('d-none',!need);if(file){const preview=document.getElementById('preview_'+id);const hasOldPhoto=preview?.dataset.hasExisting==='1';file.required=!!need&&!hasOldPhoto&&!file.files.length;}}
 radios.forEach(r=>r.addEventListener('change',updatePhoto));updatePhoto();
});
document.querySelectorAll('.foto-indikator').forEach(input=>input.addEventListener('change',()=>{const file=input.files[0],img=document.getElementById('preview_'+input.id.replace('foto_',''));if(file&&img){img.src=URL.createObjectURL(file);img.classList.remove('d-none')}}));

// Kompres foto di browser sebelum dikirim agar upload dari kamera HP lebih ringan.
const checklistForm = document.querySelector('form[action*="simpan_ceklist.php"]');
const MAX_ORIGINAL_SIZE = 10 * 1024 * 1024; // Batas foto asli sebelum kompresi: 10 MB.
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
    const photoInputs = [...checklistForm.querySelectorAll('.foto-indikator')].filter(input => input.files && input.files.length);
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
