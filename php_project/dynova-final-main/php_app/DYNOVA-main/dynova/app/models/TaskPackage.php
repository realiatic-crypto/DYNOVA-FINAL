<?php
class TaskPackage {
    public static function all(): array {
        return db()->query(
            'SELECT * FROM task_packages ORDER BY sort_order ASC, price ASC'
        )->fetchAll();
    }
    public static function active(): array {
        return db()->query(
            'SELECT * FROM task_packages WHERE is_active=1 ORDER BY sort_order ASC, price ASC'
        )->fetchAll();
    }
    public static function find(int $id): ?array {
        $s = db()->prepare('SELECT * FROM task_packages WHERE id=?');
        $s->execute([$id]);
        return $s->fetch() ?: null;
    }
    public static function save(?int $id, array $d): int {
        $cols = [
            $d['name'], $d['tier'] ?: 'standard', $d['emoji'] ?? '',
            (float)($d['price'] ?? 0), (int)($d['daily_tasks'] ?? 1),
            (float)($d['daily_earning'] ?? 0),
            (int)($d['validity_days'] ?? 30),
            (int)($d['is_featured'] ?? 0),
            (int)($d['is_active'] ?? 1),
            (int)($d['sort_order'] ?? 0),
        ];
        if ($id) {
            db()->prepare(
                'UPDATE task_packages SET name=?, tier=?, emoji=?, price=?, daily_tasks=?,
                   daily_earning=?, validity_days=?, is_featured=?, is_active=?, sort_order=?
                 WHERE id=?'
            )->execute([...$cols, $id]);
            return $id;
        }
        db()->prepare(
            'INSERT INTO task_packages
             (name, tier, emoji, price, daily_tasks, daily_earning, validity_days,
              is_featured, is_active, sort_order)
             VALUES (?,?,?,?,?,?,?,?,?,?)'
        )->execute($cols);
        return (int) db()->lastInsertId();
    }
    public static function delete(int $id): void {
        db()->prepare('DELETE FROM task_packages WHERE id=?')->execute([$id]);
    }

    /** Get the currently-active package row for a user (or null). */
    public static function activeForUser(int $uid): ?array {
        $s = db()->prepare(
            "SELECT up.*, p.name AS pkg_name, p.tier, p.emoji
             FROM user_packages up
             JOIN task_packages p ON p.id = up.package_id
             WHERE up.user_id=? AND up.status='active' AND up.expires_at > NOW()
             ORDER BY up.id DESC LIMIT 1"
        );
        $s->execute([$uid]);
        return $s->fetch() ?: null;
    }

    /** Activate a package for a user (debits balance, logs txn, inserts row). */
    public static function activate(int $uid, int $packageId): array {
        $pkg = self::find($packageId);
        if (!$pkg || !$pkg['is_active']) {
            return ['ok' => false, 'error' => 'Package not available.'];
        }
        $u = User::find($uid);
        if (!$u) return ['ok' => false, 'error' => 'User not found.'];
        if ((float) $u['balance'] < (float) $pkg['price']) {
            return ['ok' => false, 'error' =>
                'Insufficient balance. You need ' . money($pkg['price']) . ' to activate this package.'];
        }
        // Debit + log + insert in one shot
        $pdo = db();
        $pdo->beginTransaction();
        try {
            User::subtractBalance($uid, (float) $pkg['price']);
            Transaction::log(
                $uid, 'admin_adjust', -1 * (float) $pkg['price'],
                'Activated package: ' . $pkg['name']
            );
            $expires = date('Y-m-d H:i:s', time() + ((int) $pkg['validity_days']) * 86400);
            $pdo->prepare(
                'INSERT INTO user_packages
                 (user_id, package_id, daily_tasks, daily_earning, price_paid, expires_at)
                 VALUES (?,?,?,?,?,?)'
            )->execute([
                $uid, $packageId,
                (int) $pkg['daily_tasks'],
                (float) $pkg['daily_earning'],
                (float) $pkg['price'],
                $expires,
            ]);
            $pdo->commit();
            return ['ok' => true, 'expires' => $expires];
        } catch (Throwable $e) {
            $pdo->rollBack();
            return ['ok' => false, 'error' => 'Activation failed: ' . $e->getMessage()];
        }
    }
}
