<?php
require_once __DIR__.'/../config/koneksi.php'; require_role(['petugas']);
$page_title='Dashboard Petugas'; include __DIR__.'/../includes/header.php';
$rooms=$conn->query('SELECT * FROM ruangan ORDER BY nama_ruangan')->fetch_all(MYSQLI_ASSOC);
function ikon_ruangan(string $nama): string {
    $n=mb_strtolower($nama);
    return match (true) {
        str_contains($n,'pantry') => 'bi-cup-hot',
        str_contains($n,'toilet') => 'bi-droplet',
        str_contains($n,'pos satpam') => 'bi-shield-lock',
        str_contains($n,'kendaraan') => 'bi-car-front',
        str_contains($n,'klinik') || str_contains($n,'konseling') => 'bi-heart-pulse',
        str_contains($n,'mushola') || str_contains($n,'musala') => 'bi-moon-stars',
        str_contains($n,'halaman') => 'bi-tree',
        str_contains($n,'selasar') || str_contains($n,'ruang tengah') => 'bi-grid-3x3-gap',
        str_contains($n,'lobby') => 'bi-door-open',
        str_contains($n,'rapat') => 'bi-people',
        str_contains($n,'tamu') => 'bi-person-workspace',
        str_contains($n,'kerja') => 'bi-pc-display',
        default => 'bi-building',
    };
}
?>
<?php if(isset($_GET['saved'])): ?><div class="save-toast alert alert-success border-0 rounded-4 shadow-sm d-flex align-items-center gap-3" role="alert" aria-live="polite"><span class="save-toast-icon"><i class="bi bi-check-lg"></i></span><span><strong class="d-block">Ceklist berhasil <?= isset($_GET['updated'])?'diperbarui':'disimpan' ?>!</strong><span class="small">Pengecekan pukul <?= e($_GET['jam']??'') ?> sudah tersimpan.</span></span><button type="button" class="btn-close ms-auto" aria-label="Tutup" onclick="this.closest('.save-toast').remove()"></button></div><style>.save-toast{padding:16px 18px;margin-bottom:22px;background:linear-gradient(120deg,#e8f8ef,#f5fff8);color:#176b3a;border:1px solid #bce5ca!important;animation:toastIn .35s ease-out}.save-toast-icon{width:42px;height:42px;flex:0 0 42px;border-radius:14px;display:grid;place-items:center;background:#c9f0d6;color:#16713a;font-size:1.35rem}.save-toast strong{font-size:1rem}@keyframes toastIn{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:translateY(0)}}</style><script>setTimeout(()=>{const n=document.querySelector('.save-toast');if(n){n.style.transition='opacity .35s';n.style.opacity='0';setTimeout(()=>n.remove(),350)}},7000)</script><?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 dashboard-heading"><div><h1 class="h3 page-heading">Pilih Ruangan</h1><p class="muted mb-0">Pilih lokasi untuk mengisi ceklist kebersihan hari ini.</p></div><span class="dashboard-date"><i class="bi bi-calendar3 me-2"></i><?= e(tanggal_indo(date('Y-m-d'))) ?></span></div>
<div class="row g-3"><?php foreach($rooms as $r): ?><div class="col-12 col-sm-6 col-lg-4"><div class="card h-100 p-3"><div class="d-flex justify-content-between align-items-center"><span class="room-icon"><i class="bi <?= e(ikon_ruangan($r['nama_ruangan'])) ?>"></i></span></div><h2 class="h5 mt-3"><?= e(preg_replace('/\s*\([^)]*\)\s*$/u','',(string)$r['nama_ruangan'])) ?></h2><a class="btn btn-primary mt-auto" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$r['id'])) ?>">Isi Ceklist <i class="bi bi-arrow-right"></i></a></div></div><?php endforeach; ?></div>
<style>.room-icon{width:54px;height:54px;border-radius:18px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eeebff,#e3e0ff);color:#6558d9;font-size:1.55rem}.dashboard-heading{gap:16px;padding-top:8px}.dashboard-date{display:inline-flex;align-items:center;margin-top:10px;padding:10px 15px;border-radius:14px;background:rgba(255,255,255,.78);border:1px solid rgba(11,46,109,.09);color:#425579;font-size:.95rem;font-weight:600;box-shadow:0 5px 16px rgba(11,46,109,.06)}@media(max-width:575px){.dashboard-date{font-size:.9rem;margin-top:14px}}</style>
<?php include __DIR__.'/../includes/footer.php'; ?>
