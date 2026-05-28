<?php
$current = $_GET['r'] ?? 'dashboard';
$flashes = flash_pull();
$u = current_user();

// Build an "alphabetic" badge from the user's name / whatsapp — used only as a
// decorative mobile header (no greeting text, no name display).
$src = $u ? trim($u['name'] ?: $u['whatsapp']) : '';
$initial = $src !== '' ? strtoupper(mb_substr($src, 0, 1, 'UTF-8')) : '·';
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no">
<meta name="theme-color" content="#04070f">
<title><?= e(setting('site_name', APP_NAME)) ?></title>
<meta name="description" content="DYNOVA NETWORK user dashboard – rate videos, track referrals, climb salary ranks and withdraw earnings.">
<link rel="icon" type="image/jpeg" sizes="any" href="<?= asset('img/logo.jpg') ?>">
<link rel="shortcut icon" type="image/jpeg" href="<?= asset('img/logo.jpg') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/logo.jpg') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/desktop.css') ?>">
<link rel="stylesheet" href="<?= asset('css/extras.css') ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Outfit:wght@400;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body class="user-panel">
<div class="bg-fx"></div>
<div class="bg-grid"></div>
<div class="bg-orbs"><span></span><span></span><span></span><span></span><span></span></div>

<!-- ============ Desktop sidebar (>=1024px) ============ -->
<aside class="nav-desktop" data-testid="desktop-sidenav">
  <a href="<?= route_url('dashboard') ?>" class="nd-brand">
    <img src="<?= asset('img/logo.jpg') ?>" alt="DYNOVA">
    <div><b>DYNOVA</b><span>NETWORK</span></div>
  </a>
  <a href="<?= route_url('dashboard') ?>" class="nd-link <?= $current==='dashboard'?'active':'' ?>" data-testid="dnav-home"><i class="fa-solid fa-house"></i> Dashboard</a>
  <a href="<?= route_url('tasks') ?>" class="nd-link <?= str_starts_with($current,'tasks')?'active':'' ?>" data-testid="dnav-tasks"><i class="fa-solid fa-star"></i> Tasks</a>
  <a href="<?= route_url('packages') ?>" class="nd-link <?= $current==='packages'?'active':'' ?>" data-testid="dnav-packages"><i class="fa-solid fa-box-open"></i> Packages</a>
  <a href="<?= route_url('ranks') ?>" class="nd-link <?= $current==='ranks'?'active':'' ?>" data-testid="dnav-ranks"><i class="fa-solid fa-medal"></i> Salary Ranks</a>
  <a href="<?= route_url('bonuses') ?>" class="nd-link <?= $current==='bonuses'?'active':'' ?>" data-testid="dnav-bonuses"><i class="fa-solid fa-gift"></i> Joining Bonus</a>
  <a href="<?= route_url('referrals') ?>" class="nd-link <?= $current==='referrals'?'active':'' ?>" data-testid="dnav-referrals"><i class="fa-solid fa-users"></i> Referrals</a>
  <a href="<?= route_url('wallet') ?>" class="nd-link <?= str_starts_with($current,'wallet')?'active':'' ?>" data-testid="dnav-wallet"><i class="fa-solid fa-wallet"></i> Wallet</a>
  <a href="<?= route_url('profile') ?>" class="nd-link <?= str_starts_with($current,'profile')?'active':'' ?>" data-testid="dnav-profile"><i class="fa-solid fa-user"></i> Profile</a>
  <div class="nd-footer">
    <a href="<?= route_url('auth/logout') ?>" data-testid="dnav-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
  </div>
</aside>

<!-- ============ Mobile alphabetic header (< 1024px) ============ -->
<header class="mob-header" data-testid="mobile-header">
  <a href="<?= route_url('dashboard') ?>" class="mob-mark" data-testid="mob-mark">
    <span class="mob-mark-glyph"><?= e($initial) ?></span>
    <span class="mob-mark-dot"></span>
  </a>
  <a href="<?= route_url('dashboard') ?>" class="mob-brand" data-testid="mob-brand">
    <img src="<?= asset('img/logo.jpg') ?>" alt="DYNOVA">
    <span class="mob-brand-name">DYNOVA</span>
  </a>
  <a href="<?= route_url('profile') ?>" class="mob-bell" data-testid="mob-bell">
    <i class="fa-solid fa-bell"></i>
    <span class="dot"></span>
  </a>
</header>

<div class="shell page-anim">
<?php foreach ($flashes as $f): ?>
  <div class="alert <?= e($f['type']) ?>"><?= e($f['msg']) ?></div>
<?php endforeach; ?>
<?= $content ?>
</div>

<!-- ============ Mobile bottom nav (< 1024px) ============ -->
<nav class="nav-bottom" data-testid="bottom-nav">
  <a href="<?= route_url('dashboard') ?>" class="<?= $current==='dashboard'?'active':'' ?>" data-testid="nav-home"><i class="fa-solid fa-house"></i>Home</a>
  <a href="<?= route_url('tasks') ?>" class="<?= str_starts_with($current,'tasks')?'active':'' ?>" data-testid="nav-tasks"><i class="fa-solid fa-star"></i>Tasks</a>
  <a href="<?= route_url('packages') ?>" class="<?= $current==='packages'?'active':'' ?>" data-testid="nav-packages"><i class="fa-solid fa-box-open"></i>Plans</a>
  <a href="<?= route_url('ranks') ?>" class="<?= $current==='ranks'?'active':'' ?>" data-testid="nav-ranks"><i class="fa-solid fa-medal"></i>Ranks</a>
  <a href="<?= route_url('wallet') ?>" class="<?= str_starts_with($current,'wallet')?'active':'' ?>" data-testid="nav-wallet"><i class="fa-solid fa-wallet"></i>Wallet</a>
  <a href="<?= route_url('profile') ?>" class="<?= str_starts_with($current,'profile')?'active':'' ?>" data-testid="nav-profile"><i class="fa-solid fa-user"></i>Profile</a>
</nav>

<div class="copy-toast" id="copyToast">Copied to clipboard</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
