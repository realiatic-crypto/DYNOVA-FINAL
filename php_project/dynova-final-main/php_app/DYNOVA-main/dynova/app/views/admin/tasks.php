<div class="admin-h"><h1>Tasks</h1></div>

<div class="card" style="margin-bottom:18px">
  <h3 style="margin:0 0 12px">Add New Task</h3>
  <form method="post" class="stagger" data-testid="add-task-form">
    <?= csrf_field() ?>
    <input type="hidden" name="action" value="add">
    <div class="form-group"><label>Title</label>
      <input class="input" type="text" name="title" required data-testid="task-title"></div>
    <div class="form-group"><label>Video URL (YouTube)</label>
      <input class="input" type="url" name="video_url" placeholder="https://www.youtube.com/watch?v=..." required data-testid="task-url"></div>
    <div class="form-group"><label>Description</label>
      <textarea class="input" name="description" rows="2" data-testid="task-desc"></textarea></div>
    <div class="form-group"><label>Reward (PKR)</label>
      <input class="input" type="number" name="reward" step="0.01" value="50" required data-testid="task-reward"></div>
    <label class="small muted" style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
      <input type="checkbox" name="is_active" value="1" checked> Active</label>
    <button class="btn inline" type="submit" data-testid="add-task-submit">Add Task</button>
  </form>
</div>

<div class="card">
  <h3 style="margin:0 0 12px">All Tasks (<?= count($tasks) ?>)</h3>
  <table class="table" data-testid="admin-tasks-table">
    <thead><tr><th>ID</th><th>Title</th><th>Reward</th><th>Status</th><th>URL</th><th>Actions</th></tr></thead>
    <tbody>
    <?php if (!$tasks): ?><tr><td colspan="6" class="empty">No tasks yet</td></tr>
    <?php else: foreach ($tasks as $t): ?>
      <tr>
        <td>#<?= (int)$t['id'] ?></td>
        <td><?= e($t['title']) ?></td>
        <td><?= money($t['reward']) ?></td>
        <td><?php if ($t['is_active']): ?><span class="badge approved">Active</span><?php else: ?><span class="badge rejected">Off</span><?php endif; ?></td>
        <td><a href="<?= e($t['video_url']) ?>" target="_blank" style="color:var(--blue);font-size:11px">↗ view</a></td>
        <td>
          <form method="post" style="display:inline">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="btn sm ghost" name="action" value="toggle">Toggle</button>
          </form>
          <form method="post" style="display:inline" onsubmit="return confirm('Delete this task?')">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= (int)$t['id'] ?>">
            <button class="btn sm danger" name="action" value="delete" data-testid="delete-task-<?= (int)$t['id'] ?>">Delete</button>
          </form>
        </td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
