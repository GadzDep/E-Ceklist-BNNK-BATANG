<?php
require_once __DIR__ . '/../config/koneksi.php';
$page_title = $page_title ?? 'E-Ceklist BNNK Batang';
?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page_title) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--violet:#0B2E6D;--violet-dark:#082454;--ink:#17243b;--muted:#6f7b91;--line:rgba(255,255,255,.92);--glass:rgba(255,255,255,.76)}
*{box-sizing:border-box}body{min-height:100vh;color:var(--ink);font-family:'Poppins',sans-serif;background:radial-gradient(ellipse at 8% 0%,#e3ebf8 0,transparent 38%),radial-gradient(ellipse at 96% 12%,#edf2fb 0,transparent 34%),linear-gradient(135deg,#ffffff 0%,#f5f8fd 52%,#eef3fb 100%);background-attachment:fixed}
.navbar{position:sticky;top:0;z-index:1030;background:rgba(255,255,255,.58)!important;backdrop-filter:blur(28px) saturate(180%);-webkit-backdrop-filter:blur(28px) saturate(180%);border:1px solid rgba(255,255,255,.82);border-left:0;border-right:0;box-shadow:0 10px 32px rgba(11,46,109,.10)}.navbar-brand,.navbar .nav-link,.navbar-text{color:#20324f!important;font-weight:600}.navbar-brand{letter-spacing:-.3px}.navbar .nav-link{padding:.65rem .9rem;border-radius:12px}.navbar .nav-link:hover{background:rgba(11,46,109,.08);color:var(--violet)!important}.navbar-toggler{display:inline-flex!important;align-items:center;justify-content:center;width:44px;height:42px;border:1px solid rgba(11,46,109,.18)!important;border-radius:14px;background:rgba(255,255,255,.68);box-shadow:0 4px 14px rgba(80,75,160,.08)}.navbar-toggler:focus,.navbar-toggler:focus-visible{outline:0!important;box-shadow:none!important}.navbar-toggler-icon{filter:invert(15%) sepia(44%) saturate(1880%) hue-rotate(190deg) brightness(82%)}.container{max-width:1160px}.bnn-logo{width:42px;height:42px;object-fit:contain;border-radius:50%;background:#fff;padding:2px;box-shadow:0 3px 10px rgba(11,46,109,.12)}
.card{background:var(--glass);backdrop-filter:blur(20px);-webkit-backdrop-filter:blur(20px);border:1px solid var(--line);border-radius:28px;box-shadow:0 16px 42px rgba(11,46,109,.09),inset 0 1px 0 #fff}
.btn{border-radius:15px;font-weight:600;padding:.68rem 1.05rem;transition:transform .18s,box-shadow .18s}.btn:active{transform:scale(.98)}.btn-primary{background:var(--violet);border-color:var(--violet);box-shadow:0 7px 16px rgba(11,46,109,.18)}.btn-primary:hover{background:var(--violet-dark);border-color:var(--violet-dark)}.btn-outline-primary{border-color:rgba(11,46,109,.35);color:var(--violet)}
.form-control,.form-select{border:1px solid rgba(125,125,165,.15);border-radius:15px;background:rgba(255,255,255,.78);padding:.76rem .95rem}.form-control:focus,.form-select:focus{border-color:#8ca8d2;box-shadow:0 0 0 .22rem rgba(11,46,109,.12);background:#fff}
.page-heading{font-weight:750;letter-spacing:-.65px;color:#172b4b}.muted{color:var(--muted)}.badge{border-radius:999px;padding:.5em .8em;font-weight:600}.indicator-card{border:1px solid rgba(255,255,255,.95);border-radius:22px;padding:18px;background:rgba(255,255,255,.68);box-shadow:0 6px 20px rgba(77,74,140,.055)}
.choice{display:flex;gap:10px;flex-wrap:wrap}.choice label{flex:1;min-width:145px;text-align:center;padding:13px 10px;border:1px solid rgba(125,125,165,.2);border-radius:16px;font-weight:600;cursor:pointer;background:rgba(255,255,255,.82);transition:.18s}.choice input{position:absolute;opacity:0}.choice label:has(input:checked){background:linear-gradient(135deg,#174889,#0B2E6D);color:#fff;border-color:transparent;box-shadow:0 7px 18px rgba(11,46,109,.16)}
.table{--bs-table-bg:transparent}.table thead th{color:#686981;font-size:.82rem;text-transform:uppercase;letter-spacing:.035em}footer{color:#85879a!important}.form-text{color:#85879a}.photo-preview{max-width:150px;max-height:110px;object-fit:cover;border-radius:13px;border:1px solid #fff;box-shadow:0 5px 15px #5552}
@media(max-width:575.98px){main.container{padding-top:1.2rem!important}.card{border-radius:23px}.page-heading{font-size:1.5rem}.indicator-card{padding:15px}.choice label{min-width:100%;}.navbar-brand{font-size:.95rem}.bnn-logo{width:36px;height:36px}.navbar-collapse{padding:.6rem 0 .25rem}.navbar-nav{align-items:stretch!important}.navbar .nav-link{padding:.7rem .85rem}.navbar-text{padding:.5rem .85rem!important;margin:0!important}}
@media(min-width:992px){.navbar-toggler{display:none!important}.navbar-collapse{flex-grow:1}.navbar-nav{width:100%;justify-content:flex-end;align-items:center!important;gap:4px}.navbar .nav-link{white-space:nowrap}.navbar-user-chip{display:inline-flex!important;align-items:center;gap:8px;margin-right:10px!important;padding:.48rem .8rem!important;border-radius:999px;background:linear-gradient(135deg,#eaf1fb,#f5f8fd);border:1px solid rgba(11,46,109,.12);color:#0B2E6D!important;font-weight:700!important;font-size:.86rem}.navbar-user-chip i{color:#5275a8}}
.navbar-user-chip{display:inline-flex;align-items:center;gap:7px;width:max-content;max-width:100%;border-radius:12px;background:rgba(11,46,109,.06);border:1px solid rgba(11,46,109,.09);color:#0B2E6D!important;font-weight:700!important;font-size:.88rem;line-height:1.35;overflow-wrap:anywhere}
</style>
</head><body>
<nav class="navbar navbar-dark navbar-expand-lg">
<div class="container"><a class="navbar-brand d-flex align-items-center gap-2" href="<?= e(base_url(is_logged_in()?dashboard_path():'index.php')) ?>"><img src="https://upload.wikimedia.org/wikipedia/commons/c/cf/Logo_BNN.png" class="bnn-logo" alt="Logo BNN" onerror="this.style.display='none'"><span>E-Ceklist BNNK Batang</span></a>
<?php if(is_logged_in()): ?><button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navMain" aria-controls="navMain" aria-expanded="false" aria-label="Buka menu navigasi"><span class="navbar-toggler-icon"></span></button><?php endif; ?>
<div class="<?= is_logged_in() ? 'collapse navbar-collapse' : 'd-none' ?>" id="navMain"><div class="navbar-nav ms-auto align-items-lg-center">
<?php if(is_logged_in()): ?><span class="navbar-text navbar-user-chip"><i class="bi bi-person-circle"></i><span><?= e($_SESSION['user']['nama_lengkap']) ?></span></span>
<a class="nav-link" href="<?= e(base_url(dashboard_path())) ?>">Dashboard</a>
<?php if(current_role()==='admin'): ?><a class="nav-link" href="<?= e(base_url('admin/kelola_indikator.php')) ?>">Indikator</a><a class="nav-link" href="<?= e(base_url('admin/kelola_nama_user.php')) ?>">Kelola Nama User</a><?php endif; ?>
<a class="nav-link" href="<?= e(base_url('auth/logout.php')) ?>">Keluar</a>
<?php endif; ?></div></div></div></nav>
<main class="container py-4">
