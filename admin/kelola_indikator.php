<?php
require_once __DIR__.'/../config/koneksi.php';require_role(['admin']);
$rooms=$conn->query('SELECT * FROM ruangan ORDER BY nama_ruangan')->fetch_all(MYSQLI_ASSOC);$rid=(int)($_GET['ruangan']??($rooms[0]['id']??0));
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$act=$_POST['action']??'';$rid=(int)($_POST['ruangan_id']??0);
 if($act==='add'){$name=trim($_POST['nama_indikator']??'');$order=(int)($_POST['urutan']??1);if($name){$s=$conn->prepare('INSERT INTO indikator_ruangan(ruangan_id,nama_indikator,urutan,is_active) VALUES(?,?,?,1)');$s->bind_param('isi',$rid,$name,$order);$s->execute();}}
 if($act==='delete'){$id=(int)$_POST['id'];$s=$conn->prepare('DELETE FROM indikator_ruangan WHERE id=? AND ruangan_id=?');$s->bind_param('ii',$id,$rid);$s->execute();}
 redirect('admin/kelola_indikator.php?ruangan='.$rid);
}
$s=$conn->prepare('SELECT * FROM indikator_ruangan WHERE ruangan_id=? AND is_active=1 ORDER BY urutan,id');$s->bind_param('i',$rid);$s->execute();$items=stmt_rows($s);$page_title='Kelola Indikator';include __DIR__.'/../includes/header.php';
?>
<h1 class="h3 page-heading">Kelola Indikator</h1><p class="muted">Atur item pemeriksaan sesuai ruangan.</p>
<form method="get" class="card p-3 mb-3"><label class="form-label">Pilih ruangan</label><select class="form-select" name="ruangan" onchange="this.form.submit()"><?php foreach($rooms as $r): ?><option value="<?= (int)$r['id'] ?>" <?= $rid===(int)$r['id']?'selected':'' ?>><?= e($r['nama_ruangan']) ?></option><?php endforeach; ?></select></form>
<div class="card p-3 mb-3"><h2 class="h5">Tambah Indikator</h2><form method="post" class="row g-2"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="add"><input type="hidden" name="ruangan_id" value="<?= $rid ?>"><div class="col-md-8"><input class="form-control" name="nama_indikator" placeholder="Nama indikator" required></div><div class="col-md-2"><input class="form-control" type="number" min="1" name="urutan" value="<?= count($items)+1 ?>"></div><div class="col-md-2"><button class="btn btn-primary w-100">Tambah</button></div></form></div>
<div class="card p-3"><h2 class="h5">Daftar Indikator</h2><?php foreach($items as $it): ?><div class="d-flex justify-content-between align-items-center border-bottom py-2 gap-2"><span><?= (int)$it['urutan'] ?>. <?= e($it['nama_indikator']) ?></span><form method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete"><input type="hidden" name="ruangan_id" value="<?= $rid ?>"><input type="hidden" name="id" value="<?= (int)$it['id'] ?>"><button class="btn btn-sm btn-outline-danger" data-confirm="Hapus indikator ini?"><i class="bi bi-trash"></i></button></form></div><?php endforeach; ?></div>
<?php include __DIR__.'/../includes/footer.php'; ?>
