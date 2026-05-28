<?php $ladder = $ladderInfo['ladder'] ?? []; $step = (int)($ladderInfo['step'] ?? 1); $count = (int)($ladderInfo['count'] ?? 0); ?>
<div class="topbar">
  <div class="greet"><b>Withdraw Funds</b><div class="small muted">Min for your next request: <?= money($minAmount) ?></div></div>
  <a href="<?= route_url('wallet') ?>" class="bell"><i class="fa-solid fa-arrow-left"></i></a>
</div>

<div class="card stagger">
  <div class="small muted" style="text-transform:uppercase;letter-spacing:1.4px">Available to withdraw</div>
  <div class="balance-amount" data-testid="withdraw-available"><?= money($u['balance']) ?></div>
</div>

<?php if (!empty($errors)): ?>
  <div class="alert error" data-testid="withdraw-error">
    <ul><?php foreach ($errors as $err): ?><li><?= e($err) ?></li><?php endforeach; ?></ul>
  </div>
<?php endif; ?>

<form method="post" class="card stagger" data-testid="withdraw-form">
  <?= csrf_field() ?>

  <div class="form-group">
    <label>Payment Method</label>
    <div class="pm-grid" data-testid="withdraw-method-grid">
      <?php foreach ($methods as $m):
        $checked = (($_POST['method'] ?? '') === $m['name']) ? 'checked' : '';
      ?>
        <label class="pm-card" data-testid="wd-pm-<?= e($m['name']) ?>">
          <input type="radio" name="method" value="<?= e($m['name']) ?>" <?= $checked ?> required>
          <div class="pm-card-inner">
            <?= payment_logo_html($m['name'], 'md') ?>
            <div>
              <b><?= e($m['name']) ?></b>
              <div class="small muted">Tap to select</div>
            </div>
            <div class="pm-check"><i class="fa-solid fa-circle-check"></i></div>
          </div>
        </label>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="form-group">
    <label>Amount (PKR)
      <span class="small muted" style="text-transform:none;letter-spacing:.2px;font-weight:400">
        — minimum for this request: <b><?= money($minAmount) ?></b>
      </span>
    </label>
    <input class="input" type="number" name="amount" min="<?= (int)$minAmount ?>" step="1" required
           placeholder="<?= (int)$minAmount ?>" data-testid="withdraw-amount">
  </div>
  <div class="form-group">
    <label>Account Number</label>
    <input class="input" type="text" name="account_number" required data-testid="withdraw-account-number">
  </div>
  <div class="form-group">
    <label>Account Title</label>
    <input class="input" type="text" name="account_title" required data-testid="withdraw-account-title">
  </div>
  <button class="btn" type="submit" data-testid="withdraw-submit">Request Withdrawal</button>
</form>

<!-- Withdrawal ladder progress (shown below the form) -->
<div class="card stagger" data-testid="withdraw-ladder-card" style="padding:16px 18px">
  <div class="flex" style="justify-content:space-between;align-items:flex-start;gap:10px;flex-wrap:wrap;margin-bottom:10px">
    <div>
      <div class="small muted" style="letter-spacing:1.4px;text-transform:uppercase">Withdrawal limits</div>
      <h3 style="margin:2px 0 0;font-size:17px">Your next minimum: <span style="color:var(--blue,#60a5fa)" data-testid="ladder-next-min"><?= money($minAmount) ?></span></h3>
    </div>
    <div style="text-align:right">
      <div class="small muted">Withdrawals so far</div>
      <div style="font-size:16px;font-weight:800" data-testid="ladder-count"><?= (int)$count ?></div>
    </div>
  </div>
  <div class="small muted" style="margin-bottom:10px;line-height:1.5">
    Every user follows the same ladder. The minimum amount grows with each withdrawal request you make.
  </div>
  <ol class="wd-ladder" data-testid="wd-ladder-list">
    <?php foreach ($ladder as $i => $amt):
      $n = $i + 1;
      $done    = $n <  $step;          // user already cleared this rung
      $current = $n === $step;         // this is the rung they are on now
      $isLast  = $i === count($ladder) - 1;
    ?>
      <li class="wd-rung <?= $done ? 'done' : '' ?> <?= $current ? 'current' : '' ?>"
          data-testid="wd-rung-<?= $n ?>">
        <span class="wd-rung-num"><?php if ($done): ?><i class="fa-solid fa-check"></i><?php else: ?><?= $n ?><?php endif; ?></span>
        <span class="wd-rung-label">
          Withdrawal #<?= $n ?><?= $isLast ? ' & beyond' : '' ?>
        </span>
        <span class="wd-rung-amt"><?= money($amt) ?></span>
      </li>
    <?php endforeach; ?>
  </ol>
</div>

<div class="list-title"><h3>Withdrawal History</h3></div>
<div class="card" data-testid="withdraw-history">
<?php if (!$history): ?>
  <div class="empty">No withdrawals yet.</div>
<?php else: foreach ($history as $h): ?>
  <div class="activity withdrawal">
    <div class="ico"><i class="fa-solid fa-circle-up"></i></div>
    <div class="meta">
      <b><?= e($h['method']) ?> — <?= money($h['amount']) ?></b>
      <small><?= e($h['account_number']) ?> · <?= e(date('M d, H:i', strtotime($h['created_at']))) ?></small>
    </div>
    <span class="badge <?= e($h['status']) ?>"><?= e($h['status']) ?></span>
  </div>
<?php endforeach; endif; ?>
</div>

<style>
.wd-ladder{list-style:none;margin:0;padding:0;display:grid;gap:8px}
.wd-rung{
  display:grid;grid-template-columns:34px 1fr auto;align-items:center;gap:10px;
  padding:10px 12px;border-radius:12px;
  background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);
}
.wd-rung-num{
  width:30px;height:30px;border-radius:50%;display:grid;place-items:center;
  background:rgba(255,255,255,.06);color:var(--txt-mute,#9aa3b8);font-weight:700;font-size:13px;
}
.wd-rung-label{font-size:13px;color:var(--txt-mute,#9aa3b8)}
.wd-rung-amt{font-weight:800;font-size:14.5px;color:var(--txt,#fff)}
.wd-rung.done{background:rgba(16,185,129,.07);border-color:rgba(16,185,129,.25)}
.wd-rung.done .wd-rung-num{background:#10b981;color:#fff}
.wd-rung.done .wd-rung-label{color:var(--txt,#fff)}
.wd-rung.current{
  background:linear-gradient(120deg, rgba(62,182,255,.10), rgba(141,91,255,.10));
  border-color:rgba(141,91,255,.4);
  box-shadow:0 0 0 1px rgba(141,91,255,.2) inset;
}
.wd-rung.current .wd-rung-num{
  background:linear-gradient(120deg,#3eb6ff,#8d5bff);color:#fff;
  box-shadow:0 6px 18px -6px rgba(62,182,255,.7);
}
.wd-rung.current .wd-rung-label{color:var(--txt,#fff);font-weight:600}
</style>
