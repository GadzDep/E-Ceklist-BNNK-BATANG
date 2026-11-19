<?php
require_once __DIR__.'/../config/koneksi.php';
if(is_logged_in()) redirect(dashboard_path());
$page_title='Masuk - E-Ceklist BNNK Batang';
include __DIR__.'/../includes/header.php';
?>
<style>.login-bnn-logo{width:82px;height:82px;object-fit:contain;background:#fff;border-radius:22px;padding:7px;box-shadow:0 8px 24px rgba(11,46,109,.12)}.password-field{position:relative}.password-field .form-control{padding-right:3.4rem}.password-field .password-toggle{position:absolute;right:7px;top:50%;transform:translateY(-50%);z-index:5;width:40px;height:40px;border:0!important;outline:0!important;box-shadow:none!important;background:transparent!important;color:#6b7890;border-radius:12px!important}.password-field .password-toggle:focus{color:#0B2E6D;outline:0!important;box-shadow:none!important}.login-hint{display:flex;align-items:flex-start;gap:2px;padding:12px 14px;border:1px solid rgba(11,46,109,.09);border-radius:14px;background:rgba(11,46,109,.045);color:#64748b;font-size:.82rem;line-height:1.55}
</style>
<div class="row justify-content-center align-items-center" style="min-height:68vh"><div class="col-12 col-sm-9 col-md-6 col-lg-5">
<div class="card p-4 p-md-5"><div class="text-center mb-4"><div class="mb-3"><img src="https://upload.wikimedia.org/wikipedia/commons/c/cf/Logo_BNN.png" alt="Logo BNN" class="login-bnn-logo" onerror="this.style.display='none'"></div><h1 class="h3 page-heading">Selamat Datang</h1><p class="muted mb-0">Sistem Monitoring Kebersihan Ruangan</p></div>
<?php if(isset($_GET['error'])): ?><div class="alert alert-danger">Username atau password tidak sesuai.</div><?php endif; ?>
<form method="post" action="<?= e(base_url('auth/proses_login.php')) ?>">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<div class="mb-3"><label class="form-label">Username</label><input class="form-control form-control-lg" name="username" required autocomplete="username"></div>
<div class="mb-4"><label class="form-label" for="loginPassword">Password</label><div class="password-field">
<input class="form-control form-control-lg" id="loginPassword" type="password" name="password" required autocomplete="current-password">
<button class="btn password-toggle" type="button" id="togglePassword" aria-label="Tampilkan password" aria-pressed="false"><i class="bi bi-eye" id="passwordIcon"></i></button>
</div></div>
<button class="btn btn-primary btn-lg w-100">Masuk</button></form>
<script>
document.getElementById('togglePassword').addEventListener('click',function(){
 const input=document.getElementById('loginPassword'),icon=document.getElementById('passwordIcon');
 const visible=input.type==='password';input.type=visible?'text':'password';
 icon.className=visible?'bi bi-eye-slash':'bi bi-eye';
 this.setAttribute('aria-label',visible?'Sembunyikan password':'Tampilkan password');
 this.setAttribute('aria-pressed',visible?'true':'false');
});
</script>
<div class="login-hint mt-4"><i class="bi bi-info-circle me-2"></i>Pastikan username dan password yang dimasukkan sudah benar.</div></div></div></div>
<?php include __DIR__.'/../includes/footer.php'; ?>
