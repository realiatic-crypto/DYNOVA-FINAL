<div class="topbar">
  <div class="greet"><b>Withdraw Funds</b><div class="small muted">Min: <?= money($minAmount) ?></div></div>
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
    <label>Amount (PKR)</label>
    <input class="input" type="number" name="amount" min="<?= (int)$minAmount ?>" step="1" required data-testid="withdraw-amount">
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
