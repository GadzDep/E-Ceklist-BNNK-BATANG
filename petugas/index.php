<?php
require_once __DIR__.'/../config/koneksi.php'; require_role(['petugas']);
$page_title='Dashboard Petugas'; include __DIR__.'/../includes/header.php';
$rooms=$conn->query('SELECT * FROM ruangan ORDER BY nama_ruangan')->fetch_all(MYSQLI_ASSOC);
function ikon_ruangan(string $nama): string {
    $n=mb_strtolower($nama);
    return match (true) {
        str_contains($n,'pantry') => 'bi-cup-hot',
        str_contains($n,'toilet') => 'bi-droplet',
        str_contains($n,'lobby') => 'bi-door-open',
        str_contains($n,'rapat') => 'bi-people',
        str_contains($n,'tamu') => 'bi-person-workspace',
        str_contains($n,'kerja') => 'bi-pc-display',
        default => 'bi-building',
    };
}
?>
<?php if(isset($_GET['saved'])): ?><div class="alert alert-success border-0 rounded-4 shadow-sm" role="alert"><i class="bi bi-check-circle-fill me-2"></i>Ceklist jam <?= e($_GET['jam']??'') ?> berhasil <?= isset($_GET['updated'])?'diperbarui':'disimpan' ?>.</div><?php endif; ?>
<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 dashboard-heading"><div><h1 class="h3 page-heading">Pilih Ruangan</h1><p class="muted mb-0">Pilih lokasi untuk mengisi ceklist kebersihan hari ini.</p></div><span class="dashboard-date"><i class="bi bi-calendar3 me-2"></i><?= e(tanggal_indo(date('Y-m-d'))) ?></span></div>
<div class="row g-3"><?php foreach($rooms as $r): ?><div class="col-12 col-sm-6 col-lg-4"><div class="card h-100 p-3"><div class="d-flex justify-content-between align-items-center"><span class="room-icon"><i class="bi <?= e(ikon_ruangan($r['nama_ruangan'])) ?>"></i></span></div><h2 class="h5 mt-3"><?= e($r['nama_ruangan']) ?></h2><a class="btn btn-primary mt-auto" href="<?= e(base_url('petugas/form_ceklist.php?ruangan='.$r['id'])) ?>">Isi Ceklist <i class="bi bi-arrow-right"></i></a></div></div><?php endforeach; ?></div>
<style>.room-icon{width:54px;height:54px;border-radius:18px;display:inline-flex;align-items:center;justify-content:center;background:linear-gradient(135deg,#eeebff,#e3e0ff);color:#6558d9;font-size:1.55rem}.dashboard-heading{gap:16px;padding-top:8px}.dashboard-date{display:inline-flex;align-items:center;margin-top:10px;padding:10px 15px;border-radius:14px;background:rgba(255,255,255,.78);border:1px solid rgba(11,46,109,.09);color:#425579;font-size:.95rem;font-weight:600;box-shadow:0 5px 16px rgba(11,46,109,.06)}@media(max-width:575px){.dashboard-date{font-size:.9rem;margin-top:14px}}</style>
<?php include __DIR__.'/../includes/footer.php'; ?>
