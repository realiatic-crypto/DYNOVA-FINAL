<?php
class Salary {
    public static function ranks(): array {
        return db()->query('SELECT * FROM salary_ranks ORDER BY sort_order ASC, min_referrals ASC')->fetchAll();
    }
    /** Determine the highest rank the user qualifies for, or null */
    public static function rankFor(int $uid): ?array {
        $u = User::find($uid);
        if (!$u) return null;
        $refs = User::countReferrals($uid, 1);
        $business = User::teamBusiness($uid) + (float)$u['deposit_total'];
        $eligible = null;
        foreach (self::ranks() as $r) {
            if ($refs >= (int)$r['min_referrals'] && $business >= (float)$r['min_business']) {
                $eligible = $r;
            }
        }
        return $eligible;
    }
    /** Pay weekly salary to all eligible users (idempotent per week_ending Sunday) */
    public static function payWeekly(): int {
        $sunday = date('Y-m-d', strtotime('sunday this week'));
        $users = db()->query('SELECT id FROM users WHERE is_blocked=0')->fetchAll();
        $count = 0;
        foreach ($users as $row) {
            $uid = (int)$row['id'];
            $rank = self::rankFor($uid);
            if (!$rank || (float)$rank['weekly_salary'] <= 0) continue;
            try {
                db()->prepare('INSERT INTO salaries (user_id, rank_name, amount, week_ending) VALUES (?,?,?,?)')
                    ->execute([$uid, $rank['name'], $rank['weekly_salary'], $sunday]);
                User::addBalance($uid, (float)$rank['weekly_salary'], 'salary_earnings');
                db()->prepare('UPDATE users SET rank_name=? WHERE id=?')
                    ->execute([$rank['name'], $uid]);
                Transaction::log($uid, 'salary', (float)$rank['weekly_salary'], $rank['name'] . ' weekly salary');
                $count++;
            } catch (PDOException $e) {
                // duplicate (already paid this week) – ignore
            }
        }
        return $count;
    }
}
