<?php
$current = $_GET['r'] ?? 'admin/dashboard';
$flashes = flash_pull();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="theme-color" content="#04070f">
<title><?= e(setting('site_name', APP_NAME)) ?> – Admin</title>
<meta name="description" content="DYNOVA NETWORK administration console.">
<link rel="icon" type="image/jpeg" sizes="any" href="<?= asset('img/logo.jpg') ?>">
<link rel="shortcut icon" type="image/jpeg" href="<?= asset('img/logo.jpg') ?>">
<link rel="apple-touch-icon" href="<?= asset('img/logo.jpg') ?>">
<link rel="stylesheet" href="<?= asset('css/style.css') ?>">
<link rel="stylesheet" href="<?= asset('css/extras.css') ?>">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
</head>
<body>
<div class="bg-fx"></div>
<div class="bg-grid"></div>
<div class="shell admin">
  <div class="admin-shell">
    <aside class="admin-side" data-testid="admin-sidebar">
      <div class="brand" style="margin-bottom:14px">
        <div class="logo" style="width:54px;height:54px;border-radius:14px"><img src="<?= asset('img/logo.jpg') ?>" alt=""></div>
        <div class="name" style="font-size:18px;letter-spacing:3px;margin-top:6px">DYNOVA</div>
        <div class="sub" style="font-size:9px;letter-spacing:5px">A D M I N</div>
      </div>
      <h3>MENU</h3>
      <a href="<?= route_url('admin/dashboard') ?>" class="<?= $current==='admin/dashboard'||$current==='admin'?'active':'' ?>" data-testid="admin-nav-dashboard"><i class="fa-solid fa-grid-2"></i> Overview</a>
      <a href="<?= route_url('admin/users') ?>" class="<?= str_starts_with($current,'admin/users')?'active':'' ?>" data-testid="admin-nav-users"><i class="fa-solid fa-users"></i> Users</a>
      <a href="<?= route_url('admin/deposits') ?>" class="<?= $current==='admin/deposits'?'active':'' ?>" data-testid="admin-nav-deposits"><i class="fa-solid fa-money-bill-trend-up"></i> Deposits</a>
      <a href="<?= route_url('admin/withdrawals') ?>" class="<?= $current==='admin/withdrawals'?'active':'' ?>" data-testid="admin-nav-withdrawals"><i class="fa-solid fa-money-bill-transfer"></i> Withdrawals</a>
      <a href="<?= route_url('admin/tasks') ?>" class="<?= $current==='admin/tasks'?'active':'' ?>" data-testid="admin-nav-tasks"><i class="fa-solid fa-star"></i> Tasks</a>
      <a href="<?= route_url('admin/packages') ?>" class="<?= $current==='admin/packages'?'active':'' ?>" data-testid="admin-nav-packages"><i class="fa-solid fa-box-open"></i> Packages</a>
      <a href="<?= route_url('admin/bonuses') ?>" class="<?= $current==='admin/bonuses'?'active':'' ?>" data-testid="admin-nav-bonuses"><i class="fa-solid fa-gift"></i> Joining Bonuses</a>
      <a href="<?= route_url('admin/referrals') ?>" class="<?= $current==='admin/referrals'?'active':'' ?>" data-testid="admin-nav-referrals"><i class="fa-solid fa-sitemap"></i> Referrals</a>
      <a href="<?= route_url('admin/ranks') ?>" class="<?= $current==='admin/ranks'?'active':'' ?>" data-testid="admin-nav-ranks"><i class="fa-solid fa-medal"></i> Salary Ranks</a>
      <a href="<?= route_url('admin/transactions') ?>" class="<?= $current==='admin/transactions'?'active':'' ?>" data-testid="admin-nav-tx"><i class="fa-solid fa-list"></i> Transactions</a>
      <a href="<?= route_url('admin/settings') ?>" class="<?= $current==='admin/settings'?'active':'' ?>" data-testid="admin-nav-settings"><i class="fa-solid fa-gear"></i> Settings</a>
      <a href="<?= route_url('admin/logout') ?>" data-testid="admin-nav-logout"><i class="fa-solid fa-right-from-bracket"></i> Logout</a>
    </aside>
    <main class="admin-main">
      <?php
        $unlocked = function_exists('dev_unlocked') ? dev_unlocked() : false;
        $devReturn = $_GET['r'] ?? 'admin/dashboard';
      ?>
      <!-- Developer lock status banner -->
      <div class="dev-lock-bar <?= $unlocked ? 'is-unlocked' : 'is-locked' ?>" data-testid="dev-lock-bar">
        <?php if ($unlocked):
          $remaining = function_exists('dev_unlock_remaining') ? dev_unlock_remaining() : 0;
          $mins = (int)ceil($remaining / 60);
        ?>
          <div class="dev-lock-msg">
            <i class="fa-solid fa-unlock"></i>
            <span><b>Developer unlock active.</b> Write actions are allowed for the next <?= $mins ?> min<?= $mins===1?'':'s' ?>.</span>
          </div>
          <a href="<?= route_url('admin/dev-lock', ['return' => $devReturn]) ?>"
             class="dev-lock-btn lock" data-testid="dev-lock-btn">
            <i class="fa-solid fa-lock"></i> Lock now
          </a>
        <?php else: ?>
          <div class="dev-lock-msg">
            <i class="fa-solid fa-shield-halved"></i>
            <span><b>Admin is in read-only mode.</b> Adding, editing or deleting data requires a developer unlock.</span>
          </div>
          <a href="<?= route_url('admin/dev-unlock', ['return' => $devReturn]) ?>"
             class="dev-lock-btn unlock" data-testid="dev-unlock-btn">
            <i class="fa-solid fa-key"></i> Unlock to edit
          </a>
        <?php endif; ?>
      </div>

      <?php foreach ($flashes as $f): ?>
        <div class="alert <?= e($f['type']) ?>" data-testid="admin-flash"><?= e($f['msg']) ?></div>
      <?php endforeach; ?>
      <div class="<?= $unlocked ? '' : 'is-dev-locked' ?>">
        <?= $content ?>
      </div>
    </main>
  </div>
</div>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
