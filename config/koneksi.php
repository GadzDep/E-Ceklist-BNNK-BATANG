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

// Schema migration: Ya/Tidak and vehicle-specific checklist slots.
try {
    $statusCol = $conn->query("SHOW COLUMNS FROM ceklist_detail LIKE 'status'")->fetch_assoc();
    if ($statusCol && (str_contains($statusCol['Type'], 'Bersih/Rapi') || str_contains($statusCol['Type'], 'Kotor/Perlu Tindakan'))) {
        $conn->query("ALTER TABLE ceklist_detail MODIFY status ENUM('Bersih/Rapi','Kotor/Perlu Tindakan','Ya','Tidak') NOT NULL");
        $conn->query("UPDATE ceklist_detail SET status=CASE status WHEN 'Bersih/Rapi' THEN 'Ya' WHEN 'Kotor/Perlu Tindakan' THEN 'Tidak' ELSE status END");
        $conn->query("ALTER TABLE ceklist_detail MODIFY status ENUM('Ya','Tidak') NOT NULL");
    }
    $conn->query("CREATE TABLE IF NOT EXISTS kendaraan (id INT AUTO_INCREMENT PRIMARY KEY, nama VARCHAR(60) NOT NULL UNIQUE) ENGINE=InnoDB");
    $conn->query("INSERT IGNORE INTO kendaraan (id,nama) VALUES (1,'Kendaraan 1'),(2,'Kendaraan 2'),(3,'Kendaraan 3')");
    $conn->query("CREATE TABLE IF NOT EXISTS ceklist_harian_kendaraan (ceklist_harian_id INT NOT NULL, kendaraan_id INT NOT NULL, no_polisi VARCHAR(40) NULL, PRIMARY KEY (ceklist_harian_id,kendaraan_id), CONSTRAINT fk_daily_vehicle_daily FOREIGN KEY (ceklist_harian_id) REFERENCES ceklist_harian(id) ON DELETE CASCADE, CONSTRAINT fk_daily_vehicle_vehicle FOREIGN KEY (kendaraan_id) REFERENCES kendaraan(id)) ENGINE=InnoDB");
    $dcols=[]; $r=$conn->query('SHOW COLUMNS FROM ceklist_detail'); while($c=$r->fetch_assoc()) $dcols[$c['Field']]=$c;
    $addedVehicleColumn = !isset($dcols['kendaraan_id']);
    if($addedVehicleColumn) $conn->query('ALTER TABLE ceklist_detail ADD COLUMN kendaraan_id INT NULL AFTER ceklist_harian_id');
    if($addedVehicleColumn) {
        $conn->query('UPDATE ceklist_detail SET kendaraan_id=0 WHERE kendaraan_id IS NULL');
        $conn->query('UPDATE ceklist_detail d JOIN ceklist_harian h ON h.id=d.ceklist_harian_id JOIN ruangan r ON r.id=h.ruangan_id SET d.kendaraan_id=1 WHERE r.kode_qr=\'KENDARAAN-01\' AND d.kendaraan_id=0');
        $conn->query('ALTER TABLE ceklist_detail MODIFY kendaraan_id INT NOT NULL DEFAULT 0');
    } elseif (($dcols['kendaraan_id']['Null']??'')==='YES' || (string)($dcols['kendaraan_id']['Default']??'')!=='0') {
        $conn->query('UPDATE ceklist_detail SET kendaraan_id=0 WHERE kendaraan_id IS NULL');
        $conn->query('ALTER TABLE ceklist_detail MODIFY kendaraan_id INT NOT NULL DEFAULT 0');
    }
    $idx=[]; $r=$conn->query('SHOW INDEX FROM ceklist_detail'); while($c=$r->fetch_assoc()) $idx[$c['Key_name']]=true;
    if(isset($idx['unik_detail_shift_indikator'])) $conn->query('ALTER TABLE ceklist_detail DROP INDEX unik_detail_shift_indikator');
    if(!isset($idx['unik_detail_vehicle_shift_indicator'])) $conn->query('ALTER TABLE ceklist_detail ADD UNIQUE KEY unik_detail_vehicle_shift_indicator (ceklist_harian_id,kendaraan_id,jam_cek,indikator_id)');
} catch (Throwable $vehicleMigrationError) {
    error_log('[E-Ceklist] Ya/Tidak dan kendaraan migration: ' . $vehicleMigrationError->getMessage());
    http_response_code(500); exit('Penyesuaian database status/kendaraan gagal.');
}


// Add verifier-note fields to older databases without requiring a manual SQL import.
try {
    $columnSet = [];
    $res = $conn->query('SHOW COLUMNS FROM ceklist_harian');
    while ($col = $res->fetch_assoc()) $columnSet[$col['Field']] = true;
    if (!isset($columnSet['catatan_pj_sarpras'])) $conn->query('ALTER TABLE ceklist_harian ADD COLUMN catatan_pj_sarpras TEXT NULL AFTER catatan_temuan');
    if (!isset($columnSet['catatan_kasubbag_umum'])) $conn->query('ALTER TABLE ceklist_harian ADD COLUMN catatan_kasubbag_umum TEXT NULL AFTER catatan_pj_sarpras');
} catch (Throwable $migrationError) {
    error_log('[E-Ceklist] Catatan verifikator migration: ' . $migrationError->getMessage());
    http_response_code(500); exit('Penyesuaian database catatan verifikator gagal.');
}

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
