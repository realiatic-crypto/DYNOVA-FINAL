<div class="admin-h"><h1>Task Packages</h1></div>

<?php $editing = isset($_GET['edit']) ? TaskPackage::find((int)$_GET['edit']) : null; ?>

<div class="card" style="margin-bottom:18px">
  <h3 style="margin:0 0 12px"><?= $editing ? 'Edit Package #' . (int)$editing['id'] : 'Add New Package' ?></h3>
  <form method="post" class="stagger" data-testid="package-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="save">
    <?php if ($editing): ?>
      <input type="hidden" name="id" value="<?= (int)$editing['id'] ?>">
    <?php endif; ?>

    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:12px">
      <div class="form-group"><label>Name</label>
        <input class="input" type="text" name="name" required
               value="<?= e($editing['name'] ?? '') ?>" placeholder="e.g. Gold"
               data-testid="pkg-name"></div>

      <div class="form-group"><label>Tier</label>
        <select class="input" name="tier" data-testid="pkg-tier">
          <?php foreach (['starter','silver','gold','platinum','diamond','standard'] as $t): ?>
            <option value="<?= $t ?>" <?= ($editing['tier'] ?? '') === $t ? 'selected' : '' ?>>
              <?= ucfirst($t) ?>
            </option>
          <?php endforeach; ?>
        </select></div>

      <div class="form-group"><label>Emoji / Icon (optional, not shown on packages)</label>
        <input class="input" type="text" name="emoji" maxlength="4"
               value="<?= e($editing['emoji'] ?? '') ?>" placeholder="" data-testid="pkg-emoji"></div>

      <div class="form-group"><label>Price (PKR)</label>
        <input class="input" type="number" name="price" step="0.01" min="0" required
               value="<?= e($editing['price'] ?? '') ?>" placeholder="5000" data-testid="pkg-price"></div>

      <div class="form-group"><label>Daily Tasks</label>
        <input class="input" type="number" name="daily_tasks" min="1" required
               value="<?= e($editing['daily_tasks'] ?? '') ?>" placeholder="21" data-testid="pkg-daily-tasks"></div>

      <div class="form-group"><label>Daily Earning (PKR)</label>
        <input class="input" type="number" name="daily_earning" step="0.01" min="0" required
               value="<?= e($editing['daily_earning'] ?? '') ?>" placeholder="147" data-testid="pkg-daily-earning"></div>

      <div class="form-group"><label>Sort order</label>
        <input class="input" type="number" name="sort_order"
               value="<?= e($editing['sort_order'] ?? '0') ?>" data-testid="pkg-sort"></div>
    </div>

    <div class="flex" style="gap:18px;margin:6px 0 14px">
      <label class="small muted" style="display:flex;align-items:center;gap:8px">
        <input type="checkbox" name="is_active" value="1"
               <?= !isset($editing) || (int)($editing['is_active'] ?? 1) === 1 ? 'checked' : '' ?>>
        Active (visible to users)
      </label>
      <label class="small muted" style="display:flex;align-items:center;gap:8px">
        <input type="checkbox" name="is_featured" value="1"
               <?= (int)($editing['is_featured'] ?? 0) === 1 ? 'checked' : '' ?>>
        Featured (Most popular ribbon)
      </label>
    </div>

    <div class="flex" style="gap:10px">
      <button class="btn inline" type="submit" data-testid="pkg-save">
        <i class="fa-solid fa-floppy-disk"></i> <?= $editing ? 'Save changes' : 'Add Package' ?>
      </button>
      <?php if ($editing): ?>
        <a href="<?= route_url('admin/packages') ?>" class="btn ghost inline">Cancel</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<div class="card">
  <h3 style="margin:0 0 12px">All Packages (<?= count($packages) ?>)</h3>
  <table class="table" data-testid="admin-packages-table">
    <thead><tr>
      <th>ID</th><th>Name</th><th>Tier</th><th>Price</th><th>Daily tasks</th>
      <th>Daily earning</th><th>Monthly</th><th>Status</th><th>Actions</th>
    </tr></thead>
    <tbody>
    <?php if (!$packages): ?>
      <tr><td colspan="9" class="empty">No packages yet — create one above.</td></tr>
    <?php else: foreach ($packages as $p):
      $monthly = (float)$p['daily_earning'] * 30; ?>
      <tr>
        <td>#<?= (int)$p['id'] ?></td>
        <td><b><?= e($p['name']) ?></b>
          <?php if ($p['is_featured']): ?><span class="badge approved" style="margin-left:6px">★ featured</span><?php endif; ?>
        </td>
        <td><span class="badge active"><?= e($p['tier']) ?></span></td>
        <td><?= money($p['price']) ?></td>
        <td><?= (int)$p['daily_tasks'] ?></td>
        <td><?= money($p['daily_earning']) ?></td>
        <td><?= money($monthly) ?></td>
        <td><?php if ($p['is_active']): ?>
              <span class="badge approved">Active</span>
            <?php else: ?>
              <span class="badge rejected">Hidden</span>
            <?php endif; ?></td>
        <td>
          <a href="<?= route_url('admin/packages', ['edit' => $p['id']]) ?>"
             class="btn sm ghost" data-testid="pkg-edit-<?= (int)$p['id'] ?>">Edit</a>
          <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn sm ghost" name="action" value="toggle">Toggle</button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this package?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
            <button class="btn sm danger" name="action" value="delete"
                    data-testid="pkg-delete-<?= (int)$p['id'] ?>">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
