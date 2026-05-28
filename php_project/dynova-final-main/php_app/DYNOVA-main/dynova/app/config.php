<?php
/**
 * DYNOVA NETWORK – Configuration
 * --------------------------------------------------------------
 * Edit the values below for your hosting (localhost / cPanel).
 * --------------------------------------------------------------
 */

// ---- Database (PDO MySQL/MariaDB) ----
define('DB_HOST', getenv('DYNOVA_DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DYNOVA_DB_PORT') ?: '3306');
define('DB_NAME', getenv('DYNOVA_DB_NAME') ?: 'dynova_network');
define('DB_USER', getenv('DYNOVA_DB_USER') ?: 'dynova');
define('DB_PASS', getenv('DYNOVA_DB_PASS') ?: 'dynova_pass_2026');
define('DB_CHARSET', 'utf8mb4');

// ---- App ----
define('APP_NAME', 'DYNOVA NETWORK');
define('APP_CURRENCY', 'PKR');
define('APP_CURRENCY_SYMBOL', 'Rs');
// BASE_URL: the URL prefix where the app is mounted.
// • Hostinger / cPanel (production)  → leave default "" (this file).
// • Emergent preview                  → supervisor sets DYNOVA_BASE_URL=/api
//   which is picked up by getenv() below.
define('BASE_URL', getenv('DYNOVA_BASE_URL') !== false ? getenv('DYNOVA_BASE_URL') : '');

// ---- Security ----
define('SESSION_NAME', 'dynova_sess');
define('REMEMBER_DAYS', 30);

// ---- Business defaults (admin can override in admin_settings) ----
define('DEFAULT_REFERRAL_L1', 10.0);   // percent
define('DEFAULT_REFERRAL_L2', 5.0);
define('DEFAULT_REFERRAL_L3', 2.5);
define('DEFAULT_DAILY_TASK_LIMIT', 25);
define('DEFAULT_MIN_WITHDRAWAL', 500);

// ---- Timezone ----
date_default_timezone_set('Asia/Karachi');
