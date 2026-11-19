<?php
require_once __DIR__.'/config/koneksi.php';
if (is_logged_in()) redirect(dashboard_path());
redirect('auth/login.php');
