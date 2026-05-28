# DYNOVA NETWORK – Deployment & Cron Guide

This document covers how to deploy DYNOVA in production and — most importantly — how to keep the **automatic cron jobs** running with **only one cron entry**.

---

## 1. What runs automatically?

DYNOVA needs four background jobs to keep balances, ranks and deposits in good shape. They are all dispatched by a single master file: **`scheduler.php`** at the project root.

| Job | Script | Default schedule | What it does |
|-----|--------|------------------|--------------|
| `daily_reset` | `cron/daily_reset.php` | every day **00:05** | Sweeps stale sessions & remember-tokens, writes daily KPI snapshot to `/logs/cron.log`. |
| `process_deposits` | `cron/process_deposits.php` | every hour | Auto-rejects pending deposits older than **24 h** (never auto-approves). |
| `weekly_bonus` | `cron/weekly_bonus.php` | every **Monday 02:00** | Recomputes every user's rank and audits last-7-days referral bonuses — credits anything the live job missed. |
| `monthly_salary` | `cron/monthly_salary.php` | **1st of each month, 03:00** | Distributes a salary to every user who currently qualifies for a rank. Idempotent — running it twice in a month is safe. |

All four scripts write to **`/logs/cron.log`** so the client can see everything from one file.

> **The client does NOT need to register four separate cron jobs.**
> One entry per minute that hits `scheduler.php` is enough — it figures out which jobs are due itself.

---

## 2. Installation (optional, recommended)

`scheduler.php` works **with or without Composer**. If you want the production-grade `peppeocchi/php-cron-scheduler`, run once at deploy time:

```bash
cd /home/USER/dynova
composer install --no-dev --optimize-autoloader
```

If Composer is unavailable on the host, do nothing — `scheduler.php` falls back to a built-in cron-expression engine that understands the same syntax. The result is identical.

---

## 3. The ONE cron entry the client needs

### Option A — cPanel (most Pakistani shared hosts)

1. Log in to cPanel.
2. Open **Cron Jobs**.
3. Add a new cron job with these exact values:

```
Minute:        *
Hour:          *
Day:           *
Month:         *
Weekday:       *
Command:       /usr/bin/php /home/USER/dynova/scheduler.php >> /home/USER/dynova/logs/cron.log 2>&1
```

Replace `USER` with your cPanel username. Save.

That's it. The scheduler now ticks every minute and only fires jobs at their scheduled times.

> **Tip:** If your host's PHP binary is at a non-standard path, use `which php` over SSH or check cPanel's "PHP Selector" for the right binary (often `/usr/local/bin/php` or `/usr/bin/php8.2`).

---

### Option B — cron-job.org (zero-host, no SSH required)

Use this if the host has **no cron support** (some free / very cheap hosts).

1. Expose a tiny web endpoint by creating `dynova/public/_tick.php`:

   ```php
   <?php
   // Restrict to a secret token so randoms can't spam the scheduler.
   if (($_GET['key'] ?? '') !== 'CHANGE-ME-TO-A-LONG-RANDOM-STRING') {
       http_response_code(403); exit('Forbidden');
   }
   require __DIR__ . '/../scheduler.php';
   ```

   Change the secret to something only you know (e.g. 32 random hex chars).

2. Visit <https://cron-job.org> → create a free account.

3. Add a new cronjob with:
   - **URL:** `https://yourdomain.com/_tick.php?key=YOUR-SECRET`
   - **Schedule:** "Every minute" (1-minute interval, free tier allows this).
   - **Save**.

cron-job.org will hit the URL every minute and dispatch any due jobs.

---

### Option C — No cron at all (fallback)

If the host has neither real cron nor allows external HTTP calls (very rare), DYNOVA degrades gracefully:

- **Salaries** can be triggered manually from the admin panel (admin user → settings → "Pay this month's salary").
- **Stale deposits** can be left in pending state — admins simply approve/reject from `/api/?r=admin/deposits` as they would today.
- **Referral bonuses** are already credited LIVE the moment the source user submits a task rating; the weekly audit is purely a safety net.

You can also run the scheduler **on demand** from SSH whenever you remember:

```bash
php /path/to/dynova/scheduler.php run all       # runs every job once, ignoring schedule
php /path/to/dynova/scheduler.php run daily_reset
php /path/to/dynova/scheduler.php list          # shows the schedule
```

The product still works — you just lose the "set and forget" automation.

---

## 4. Verifying it works

After deploying, wait one minute then check the log:

```bash
tail -n 50 /home/USER/dynova/logs/cron.log
```

You should see lines like:

```
[2026-05-26 12:00:00] [INFO ] [scheduler] tick – nothing due.
[2026-05-26 12:05:00] [INFO ] [scheduler] Dispatch -> daily_reset
[2026-05-26 12:05:00] [OK   ] [daily_reset] Job finished in 187 ms.
```

To force a smoke test:

```bash
php /path/to/dynova/scheduler.php run daily_reset
```

Check `/logs/cron.log` — you'll see the dispatch + job lines appear immediately.

---

## 5. Quick reference — file map

```
dynova/
├── scheduler.php              ← the ONE entry every cron hits
├── composer.json              ← peppeocchi/php-cron-scheduler (optional)
├── cron/
│   ├── _bootstrap.php         ← shared DB + log helpers
│   ├── daily_reset.php
│   ├── process_deposits.php
│   ├── weekly_bonus.php
│   ├── monthly_salary.php
│   └── weekly_salary.php      ← legacy (kept for backward-compat)
└── logs/
    └── cron.log               ← human-readable trace of everything
```

---

## 6. Troubleshooting

| Symptom | Likely cause | Fix |
|---------|--------------|-----|
| `cron.log` never gets new lines | cron entry uses wrong PHP path or wrong project path | Run the command manually from SSH — if it works there, fix the path in cPanel. |
| `Permission denied … logs/cron.log` | Web user can't write to `/logs/` | `chmod 775 /home/USER/dynova/logs` and ensure the user running cron owns it. |
| Logs show "tick – nothing due" forever | Server timezone differs from `config.php` (`Asia/Karachi`) | Either align the host timezone or update the timezone in `app/config.php`. |
| Duplicate salary entries | Running the legacy `cron/weekly_salary.php` AND the new `monthly_salary.php` at the same time | Pick one cadence (weekly or monthly) and remove the other from the schedule in `scheduler.php`. |

---

**Bottom line:** install once → add ONE cron line → check `/logs/cron.log` after 24 hours. Everything else is automatic.
