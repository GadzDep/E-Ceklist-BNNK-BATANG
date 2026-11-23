<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['admin']);
$roles=['petugas'=>'Petugas Kebersihan','pj_sarpras'=>'PJ Sarpras','kasubbag_umum'=>'Kasubbag Umum'];
$notice='';
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$role=$_POST['role']??'';$name=trim($_POST['nama_lengkap']??'');if(isset($roles[$role])&&$name!==''&&mb_strlen($name)<=120){$s=$conn->prepare('UPDATE users SET nama_lengkap=? WHERE role=?');$s->bind_param('ss',$name,$role);$s->execute();$notice='Nama user berhasil diperbarui.';}else{$notice='Nama tidak valid. Maksimal 120 karakter.';}}
$users=[];$s=$conn->prepare("SELECT id,nama_lengkap,username,role FROM users WHERE role IN ('petugas','pj_sarpras','kasubbag_umum') ORDER BY FIELD(role,'petugas','pj_sarpras','kasubbag_umum')");$s->execute();foreach(stmt_rows($s) as $u)$users[$u['role']]=$u;
$page_title='Kelola Nama User';include __DIR__.'/../includes/header.php';
?>
<h1 class="h3 page-heading">Kelola Nama User</h1><p class="muted mb-4">Nama otomatis digunakan pada ceklist, verifikasi, dan formulir cetak.</p>
<?php if($notice): ?><div class="alert alert-info rounded-4"><?= e($notice) ?></div><?php endif; ?>
<div class="row g-3"><?php foreach($roles as $role=>$label):$u=$users[$role]??null; ?><div class="col-12 col-lg-4"><div class="card p-3 p-md-4 h-100"><h2 class="h5 fw-bold mb-1"><?= e($label) ?></h2><div class="small text-secondary mb-3">Username: <?= e($u['username']??'') ?></div><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="role" value="<?= e($role) ?>"><label class="form-label">Nama lengkap</label><input class="form-control mb-3" name="nama_lengkap" maxlength="120" required value="<?= e($u['nama_lengkap']??'') ?>"><button class="btn btn-primary w-100">Simpan Nama</button></form></div></div><?php endforeach; ?></div>
<div class="mt-3"><a class="btn btn-outline-secondary" href="<?= e(base_url('admin/kelola_ruangan.php')) ?>"><i class="bi bi-arrow-left me-1"></i> Kembali</a></div>
<?php include __DIR__.'/../includes/footer.php'; ?>
