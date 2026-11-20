<?php
declare(strict_types=1);
if (session_status() !== PHP_SESSION_ACTIVE) { session_start(); }
date_default_timezone_set('Asia/Jakarta'); // Pastikan tanggal ceklist mengikuti WIB (Indonesia).

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'db_ceklist';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    http_response_code(500);
    exit('Koneksi database gagal. Periksa konfigurasi config/koneksi.php.');
}
$conn->set_charset('utf8mb4');

function e($value): string {
    return htmlspecialchars((string)($value ?? ''), ENT_QUOTES, 'UTF-8');
}
function base_url(string $path = ''): string {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/e-ceklist/index.php');
    $root = preg_replace('~/(auth|petugas|verifikator|admin|laporan)/.*$~', '', $script);
    if ($root === $script && preg_match('~^/.+\\.php$~', $root)) {
        $root = dirname($root);
    }
    $root = rtrim($root, '/');
    return ($root ?: '') . '/' . ltrim($path, '/');
}
function redirect(string $path): never {
    header('Location: ' . base_url($path));
    exit;
}
function is_logged_in(): bool { return isset($_SESSION['user']['id']); }
function current_role(): string { return (string)($_SESSION['user']['role'] ?? ''); }
function require_login(): void {
    if (!is_logged_in()) redirect('auth/login.php');
}
function require_role(array $roles): void {
    require_login();
    if (!in_array(current_role(), $roles, true)) {
        http_response_code(403);
        exit('Akses ditolak.');
    }
}
function dashboard_path(): string {
    return match (current_role()) {
        'petugas' => 'petugas/index.php',
        'pj_sarpras', 'kasubbag_umum' => 'verifikator/dashboard.php',
        'admin' => 'admin/kelola_ruangan.php',
        default => 'auth/login.php'
    };
}
function tanggal_indo(string $date): string {
    $bulan = [1=>'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $ts = strtotime($date);
    return date('j', $ts) . ' ' . $bulan[(int)date('n', $ts)] . ' ' . date('Y', $ts);
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' &&
        !hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
        http_response_code(419); exit('Token keamanan tidak valid. Muat ulang halaman.');
    }
}
function stmt_rows(mysqli_stmt $stmt): array {
    $result = $stmt->get_result();
    return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
}
