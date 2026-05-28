<div class="admin-h">
  <h1>Salary Ranks</h1>
  <form method="post" style="display:inline">
    <?= csrf_field() ?>
    <button class="btn inline" name="action" value="pay_now" type="submit" onclick="return confirm('Pay this week\'s salaries to all eligible users now?')" data-testid="pay-now">Run Weekly Payout Now</button>
  </form>
</div>

<div class="card" style="margin-bottom:18px">
  <h3 style="margin:0 0 12px">Existing Ranks</h3>
  <table class="table" data-testid="admin-ranks-table">
    <thead><tr><th>Rank</th><th>Min Refs</th><th>Min Business</th><th>Weekly Salary</th><th>Order</th><th></th></tr></thead>
    <tbody>
    <?php foreach ($ranks as $r): ?>
      <tr>
        <td><?= e($r['emoji']) ?> <b><?= e($r['name']) ?></b></td>
        <td><?= (int)$r['min_referrals'] ?></td>
        <td><?= money($r['min_business']) ?></td>
        <td><?= money($r['weekly_salary']) ?></td>
        <td><?= (int)$r['sort_order'] ?></td>
        <td>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this rank?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn sm danger" name="action" value="delete">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="card">
  <h3 style="margin:0 0 12px">Add / Edit Rank</h3>
  <form method="post" data-testid="rank-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <div class="admin-grid" style="grid-template-columns:repeat(3,1fr)">
      <div class="form-group"><label>ID (blank = new)</label>
        <input class="input" type="number" name="id" placeholder="leave blank to add"></div>
      <div class="form-group"><label>Name</label>
        <input class="input" type="text" name="name" required data-testid="rank-name"></div>
      <div class="form-group"><label>Emoji</label>
        <input class="input" type="text" name="emoji" placeholder="🥉 🥈 🥇 💎"></div>
      <div class="form-group"><label>Min Referrals</label>
        <input class="input" type="number" name="min_referrals" value="0" required></div>
      <div class="form-group"><label>Min Business (PKR)</label>
        <input class="input" type="number" name="min_business" step="0.01" value="0" required></div>
      <div class="form-group"><label>Weekly Salary (PKR)</label>
        <input class="input" type="number" name="weekly_salary" step="0.01" value="0" required></div>
      <div class="form-group"><label>Sort Order</label>
        <input class="input" type="number" name="sort_order" value="0"></div>
    </div>
    <button class="btn inline" type="submit" data-testid="rank-save">Save Rank</button>
  </form>
</div>
