<?php
/**
 * Authentication helpers – user + admin session management.
 */

function current_user(): ?array {
    if (!empty($_SESSION['user_id'])) {
        static $user = null;
        if ($user === null) {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch() ?: null;
        }
        return $user;
    }
    // Try remember-me cookie
    if (!empty($_COOKIE['dn_remember'])) {
        [$uid, $token] = array_pad(explode(':', $_COOKIE['dn_remember'], 2), 2, '');
        if ($uid && $token) {
            $stmt = db()->prepare('SELECT * FROM users WHERE id = ? AND remember_token = ?');
            $stmt->execute([$uid, hash('sha256', $token)]);
            if ($u = $stmt->fetch()) {
                $_SESSION['user_id'] = $u['id'];
                return $u;
            }
        }
    }
    return null;
}

function require_user(): array {
    $u = current_user();
    if (!$u) { redirect('auth/login'); }
    if ((int)$u['is_blocked'] === 1) {
        session_destroy();
        die('Your account is blocked. Contact support.');
    }
    return $u;
}

function current_admin(): ?array {
    if (!empty($_SESSION['admin_id'])) {
        $stmt = db()->prepare('SELECT * FROM admins WHERE id = ?');
        $stmt->execute([$_SESSION['admin_id']]);
        return $stmt->fetch() ?: null;
    }
    return null;
}

function require_admin(): array {
    $a = current_admin();
    if (!$a) { redirect('admin/login'); }
    return $a;
}

function logout_user(): void {
    if (!empty($_SESSION['user_id'])) {
        db()->prepare('UPDATE users SET remember_token = NULL WHERE id = ?')
            ->execute([$_SESSION['user_id']]);
    }
    setcookie('dn_remember', '', time() - 3600, '/');
    unset($_SESSION['user_id']);
}
