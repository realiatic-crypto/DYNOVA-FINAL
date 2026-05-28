<?php
class DashboardController {
    public function index(): void {
        $u = require_user();
        $todayEarnings = Transaction::todayEarnings((int)$u['id']);
        $referralCount = User::countReferrals((int)$u['id'], 1)
                       + User::countReferrals((int)$u['id'], 2)
                       + User::countReferrals((int)$u['id'], 3);
        $pendingWd = Withdrawal::pendingSumForUser((int)$u['id']);
        $completedToday = Task::completedTodayCount((int)$u['id']);
        $dailyLimit = (int)setting('daily_task_limit', DEFAULT_DAILY_TASK_LIMIT);
        $recent = Transaction::forUser((int)$u['id'], 6);
        view('user/dashboard', compact('u','todayEarnings','referralCount','pendingWd','completedToday','dailyLimit','recent'), 'app');
    }
}
