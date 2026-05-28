<?php
class AdminController {
    // -------------------------------------------------- LOGIN
    public function login(): void {
        if (current_admin()) redirect('admin/dashboard');
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $pass  = $_POST['password'] ?? '';
            $s = db()->prepare('SELECT * FROM admins WHERE email=?');
            $s->execute([$email]);
            $a = $s->fetch();
            if (!$a || !password_verify($pass, $a['password_hash'])) {
                $errors[] = 'Invalid admin credentials.';
            } else {
                $_SESSION['admin_id'] = $a['id'];
                redirect('admin/dashboard');
            }
        }
        view('admin/login', compact('errors'));
    }

    public function logout(): void {
        unset($_SESSION['admin_id']);
        redirect('admin/login');
    }

    // -------------------------------------------------- DASHBOARD
    public function dashboard(): void {
        require_admin();
        $stats = [
            'total_users'        => (int)db()->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'total_deposits'     => (float)db()->query("SELECT COALESCE(SUM(amount),0) FROM deposits WHERE status='approved'")->fetchColumn(),
            'pending_deposits'   => (int)db()->query("SELECT COUNT(*) FROM deposits WHERE status='pending'")->fetchColumn(),
            'pending_wd'         => (int)db()->query("SELECT COUNT(*) FROM withdrawals WHERE status='pending'")->fetchColumn(),
            'today_earnings'     => (float)db()->query(
                "SELECT COALESCE(SUM(amount),0) FROM transactions
                 WHERE type IN ('task','referral','salary') AND DATE(created_at)=CURDATE()"
            )->fetchColumn(),
            'total_tasks'        => (int)db()->query('SELECT COUNT(*) FROM tasks')->fetchColumn(),
            'completions_today'  => (int)db()->query('SELECT COUNT(*) FROM task_completions WHERE DATE(created_at)=CURDATE()')->fetchColumn(),
        ];
        // 7-day chart
        $rows = db()->query(
            "SELECT DATE(created_at) d, COALESCE(SUM(amount),0) total
             FROM transactions WHERE type IN ('task','referral','salary')
              AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
             GROUP BY DATE(created_at) ORDER BY d ASC"
        )->fetchAll();
        $chart = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = date('Y-m-d', strtotime("-$i days"));
            $chart[$d] = 0;
        }
        foreach ($rows as $r) { $chart[$r['d']] = (float)$r['total']; }
        $pendingWd = Withdrawal::pending();
        view('admin/dashboard', compact('stats','chart','pendingWd'), 'admin');
    }

    // -------------------------------------------------- USERS
    public function users(): void {
        require_admin();
        $q = trim($_GET['q'] ?? '');
        if ($q !== '') {
            $s = db()->prepare("SELECT * FROM users WHERE whatsapp LIKE ? OR name LIKE ? OR referral_code LIKE ? ORDER BY id DESC LIMIT 200");
            $s->execute(["%$q%","%$q%","%$q%"]);
        } else {
            $s = db()->query('SELECT * FROM users ORDER BY id DESC LIMIT 200');
        }
        $users = $s->fetchAll();
        view('admin/users', compact('users','q'), 'admin');
    }
    public function userEdit(): void {
        require_admin();
        $id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
        $u = User::find($id);
        if (!$u) { flash_set('error','User not found.'); redirect('admin/users'); }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            if ($action === 'block') {
                db()->prepare('UPDATE users SET is_blocked=1 WHERE id=?')->execute([$id]);
                flash_set('success','User blocked.');
            } elseif ($action === 'unblock') {
                db()->prepare('UPDATE users SET is_blocked=0 WHERE id=?')->execute([$id]);
                flash_set('success','User unblocked.');
            } elseif ($action === 'adjust') {
                $amt  = (float)($_POST['amount'] ?? 0);
                $note = trim($_POST['note'] ?? '');
                if ($amt != 0) {
                    db()->prepare('UPDATE users SET balance = balance + ? WHERE id=?')->execute([$amt, $id]);
                    Transaction::log($id, 'admin_adjust', $amt, $note ?: 'Admin balance adjust');
                    flash_set('success','Balance adjusted by ' . money($amt));
                }
            }
            redirect('admin/users/edit', ['id' => $id]);
        }
        $tx = Transaction::forUser($id, 30);
        view('admin/user_edit', compact('u','tx'), 'admin');
    }

    // -------------------------------------------------- DEPOSITS
    public function deposits(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $act = $_POST['action'] ?? '';
            $d = Deposit::find($id);
            if ($d && $d['status'] === 'pending') {
                if ($act === 'approve') {
                    Deposit::setStatus($id, 'approved', $_POST['note'] ?? null);
                    User::addBalance((int)$d['user_id'], (float)$d['amount'], 'balance');
                    User::addBalance((int)$d['user_id'], (float)$d['amount'], 'deposit_total');
                    Transaction::log((int)$d['user_id'], 'deposit', (float)$d['amount'], 'Deposit via ' . $d['method']);
                    flash_set('success','Deposit approved.');
                } elseif ($act === 'reject') {
                    Deposit::setStatus($id, 'rejected', $_POST['note'] ?? null);
                    flash_set('success','Deposit rejected.');
                }
            }
            redirect('admin/deposits');
        }
        $pending = Deposit::pending();
        $all = Deposit::all();
        view('admin/deposits', compact('pending','all'), 'admin');
    }

    // -------------------------------------------------- WITHDRAWALS
    public function withdrawals(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $act = $_POST['action'] ?? '';
            $w = Withdrawal::find($id);
            if ($w && $w['status'] === 'pending') {
                if ($act === 'paid' || $act === 'approve') {
                    Withdrawal::setStatus($id, 'paid', $_POST['note'] ?? null);
                    flash_set('success','Withdrawal marked as paid.');
                } elseif ($act === 'reject') {
                    Withdrawal::setStatus($id, 'rejected', $_POST['note'] ?? null);
                    // refund amount back to user balance
                    User::addBalance((int)$w['user_id'], (float)$w['amount'], 'balance');
                    Transaction::log((int)$w['user_id'], 'admin_adjust', (float)$w['amount'], 'Withdrawal refund');
                    flash_set('success','Withdrawal rejected and amount refunded.');
                }
            }
            redirect('admin/withdrawals');
        }
        $pending = Withdrawal::pending();
        $all = Withdrawal::all();
        view('admin/withdrawals', compact('pending','all'), 'admin');
    }

    // -------------------------------------------------- TASKS
    public function tasks(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['action'] ?? '';
            if ($act === 'add' || $act === 'edit') {
                $id     = (int)($_POST['id'] ?? 0);
                $title  = trim($_POST['title'] ?? '');
                $url    = trim($_POST['video_url'] ?? '');
                $desc   = trim($_POST['description'] ?? '');
                $reward = (float)($_POST['reward'] ?? 0);
                $active = isset($_POST['is_active']) ? 1 : 0;
                if ($title && $url && $reward > 0) {
                    if ($id) {
                        db()->prepare('UPDATE tasks SET title=?, video_url=?, description=?, reward=?, is_active=? WHERE id=?')
                            ->execute([$title,$url,$desc,$reward,$active,$id]);
                    } else {
                        db()->prepare('INSERT INTO tasks (title, video_url, description, reward, is_active) VALUES (?,?,?,?,?)')
                            ->execute([$title,$url,$desc,$reward,$active]);
                    }
                    flash_set('success','Task saved.');
                }
            } elseif ($act === 'delete') {
                db()->prepare('DELETE FROM tasks WHERE id=?')->execute([(int)$_POST['id']]);
                flash_set('success','Task deleted.');
            } elseif ($act === 'toggle') {
                db()->prepare('UPDATE tasks SET is_active = 1 - is_active WHERE id=?')->execute([(int)$_POST['id']]);
            }
            redirect('admin/tasks');
        }
        $tasks = Task::all();
        view('admin/tasks', compact('tasks'), 'admin');
    }

    // -------------------------------------------------- REFERRAL TREE
    public function referrals(): void {
        require_admin();
        $id = (int)($_GET['user_id'] ?? 0);
        $user = $id ? User::find($id) : null;
        $teamA = $teamB = $teamC = [];
        if ($user) {
            $teamA = Referral::levelMembers($id, 1);
            $teamB = Referral::levelMembers($id, 2);
            $teamC = Referral::levelMembers($id, 3);
        }
        view('admin/referrals', compact('user','teamA','teamB','teamC'), 'admin');
    }

    // -------------------------------------------------- SETTINGS
    public function settings(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section = $_POST['section'] ?? '';
            if ($section === 'general') {
                foreach (['referral_l1','referral_l2','referral_l3','daily_task_limit','min_withdrawal','site_name','site_tagline'] as $k) {
                    if (isset($_POST[$k])) setting_set($k, $_POST[$k]);
                }
                flash_set('success','Settings updated.');
            } elseif ($section === 'pm_save') {
                PaymentMethod::save((int)($_POST['id'] ?? 0) ?: null, [
                    'name'           => $_POST['name'] ?? '',
                    'account_title'  => $_POST['account_title'] ?? '',
                    'account_number' => $_POST['account_number'] ?? '',
                    'instructions'   => $_POST['instructions'] ?? '',
                    'is_active'      => isset($_POST['is_active']) ? 1 : 0,
                ]);
                flash_set('success','Payment method saved.');
            } elseif ($section === 'pm_delete') {
                PaymentMethod::delete((int)$_POST['id']);
                flash_set('success','Payment method deleted.');
            }
            redirect('admin/settings');
        }
        $methods = PaymentMethod::all();
        $values = [
            'referral_l1'      => setting('referral_l1', DEFAULT_REFERRAL_L1),
            'referral_l2'      => setting('referral_l2', DEFAULT_REFERRAL_L2),
            'referral_l3'      => setting('referral_l3', DEFAULT_REFERRAL_L3),
            'daily_task_limit' => setting('daily_task_limit', DEFAULT_DAILY_TASK_LIMIT),
            'min_withdrawal'   => setting('min_withdrawal', DEFAULT_MIN_WITHDRAWAL),
            'site_name'        => setting('site_name', APP_NAME),
            'site_tagline'     => setting('site_tagline', 'Rate. Earn. Refer.'),
        ];
        view('admin/settings', compact('values','methods'), 'admin');
    }

    // -------------------------------------------------- RANKS
    public function ranks(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['action'] ?? '';
            if ($act === 'save') {
                $id = (int)($_POST['id'] ?? 0);
                $data = [
                    $_POST['name'] ?? '',
                    $_POST['emoji'] ?? '',
                    (int)($_POST['min_referrals'] ?? 0),
                    (float)($_POST['min_business'] ?? 0),
                    (float)($_POST['weekly_salary'] ?? 0),
                    (int)($_POST['sort_order'] ?? 0),
                ];
                if ($id) {
                    db()->prepare('UPDATE salary_ranks SET name=?, emoji=?, min_referrals=?, min_business=?, weekly_salary=?, sort_order=? WHERE id=?')
                        ->execute([...$data, $id]);
                } else {
                    db()->prepare('INSERT INTO salary_ranks (name,emoji,min_referrals,min_business,weekly_salary,sort_order) VALUES (?,?,?,?,?,?)')
                        ->execute($data);
                }
                flash_set('success','Rank saved.');
            } elseif ($act === 'delete') {
                db()->prepare('DELETE FROM salary_ranks WHERE id=?')->execute([(int)$_POST['id']]);
                flash_set('success','Rank deleted.');
            } elseif ($act === 'pay_now') {
                $n = Salary::payWeekly();
                flash_set('success', "Salaries paid to $n users.");
            }
            redirect('admin/ranks');
        }
        $ranks = Salary::ranks();
        view('admin/ranks', compact('ranks'), 'admin');
    }

    // -------------------------------------------------- PACKAGES
    public function packages(): void {
        require_admin();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $act = $_POST['action'] ?? '';
            if ($act === 'save') {
                TaskPackage::save((int)($_POST['id'] ?? 0) ?: null, $_POST);
                flash_set('success', 'Package saved.');
            } elseif ($act === 'delete') {
                TaskPackage::delete((int)($_POST['id'] ?? 0));
                flash_set('success', 'Package deleted.');
            } elseif ($act === 'toggle') {
                db()->prepare('UPDATE task_packages SET is_active = 1 - is_active WHERE id=?')
                    ->execute([(int)($_POST['id'] ?? 0)]);
            }
            redirect('admin/packages');
        }
        $packages = TaskPackage::all();
        view('admin/packages', compact('packages'), 'admin');
    }

    // -------------------------------------------------- TRANSACTIONS
    public function transactions(): void {
        require_admin();
        $type = $_GET['type'] ?? '';
        $allowed = ['deposit','task','referral','salary','withdrawal','admin_adjust'];
        if ($type && in_array($type, $allowed, true)) {
            $s = db()->prepare(
                'SELECT t.*, u.whatsapp, u.name FROM transactions t JOIN users u ON u.id=t.user_id
                 WHERE t.type=? ORDER BY t.id DESC LIMIT 300'
            );
            $s->execute([$type]);
        } else {
            $s = db()->query(
                'SELECT t.*, u.whatsapp, u.name FROM transactions t JOIN users u ON u.id=t.user_id
                 ORDER BY t.id DESC LIMIT 300'
            );
        }
        $rows = $s->fetchAll();
        view('admin/transactions', compact('rows','type','allowed'), 'admin');
    }
}
