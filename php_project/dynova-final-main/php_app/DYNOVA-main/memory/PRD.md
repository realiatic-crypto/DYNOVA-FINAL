# DYNOVA NETWORK – PRD

## Original Problem Statement
Build a complete production-ready PHP project (no framework required). The platform is a daily video-rating earning website that behaves like a mobile app. Native currency: **PKR**.

User-supplied design: dark theme, blue / cyan + violet accents, flowing lights, lots of animation. Logo: DYNOVA NETWORK (provided).

User-supplied admin credentials: `admin@dynova.com` / `password`.

Admin can configure: referral percentages, payment methods, ranks/salary, and all other business parameters.

## Architecture
- **Server runtime:** PHP 8.2 + MariaDB 10.11 (CLI-managed by supervisor).
- **MVC-like layout:** front-controller (`public/index.php`) with `?r=route` style URLs, autoloaded models & controllers.
- **Emergent preview:** mounted under `/api/` via FastAPI reverse-proxy → local PHP `:9000`. React frontend at `/` redirects to `/api/`. The PHP project can also be exported as-is for cPanel (set `BASE_URL=""`).
- **Security:** PDO prepared statements, bcrypt password hashing, CSRF tokens on every POST, htmlspecialchars on every output, HttpOnly + SameSite session cookies, separate user/admin sessions.

## Personas
1. **End-user (Pakistani earner):** signs up with WhatsApp + password, rates daily videos for PKR, deposits via JazzCash/EasyPesa, refers friends, withdraws once min reached.
2. **Admin:** approves money flow, manages tasks, ranks, payment methods, watches the ledger.

## Implemented (2026-02 / current run)
- Auth: signup (with captcha + referral capture), login (with remember-me), logout.
- Mobile-first dashboard, bottom navigation.
- Video task system with daily limit, one-task-at-a-time gating, YouTube embed.
- Star rating + reward credit + multi-level (L1/L2/L3) referral commissions auto-credited.
- 3-level referral page with daily/weekly/yearly toggle and avatar groups.
- Deposit submission (manual TID), admin approve/reject, balance credit + lifetime volume tracking.
- Withdrawal request (deducts immediately), admin paid/reject (reject refunds), history.
- Salary engine: configurable ranks, weekly cron-payable salary, idempotent per week.
- Admin panel: KPI dashboard with 7-day chart, users (block/unblock + balance adjust), deposits, withdrawals, tasks CRUD, referral tree, settings (percentages, min withdrawal, daily limit), payment methods CRUD, ranks CRUD, transactions ledger with filters.
- All forms CSRF-protected, all output XSS-safe.
- Animated dark UI: gradient orbs, particle "rising" lights, shimmer cards, pulse glows, staggered entry animations.
- Downloadable cPanel-ready zip at `/api/dynova-network.zip`.

## Tech files
- `/app/dynova/**` — entire PHP project (web root: `public/`).
- `/app/dynova/sql/schema.sql` — full schema + seed.
- `/app/backend/server.py` — FastAPI reverse-proxy → PHP.
- `/app/frontend/src/App.js` — redirects to `/api/`.
- `/etc/supervisor/conf.d/dynova.conf` — supervisor configs for `mariadb` + `php`.

## Roadmap / Backlog (P0 → P2)
- **P1:** Forgot-password (SMS OTP via Twilio).
- **P1:** Cool-down between task submissions, watch-duration verification.
- **P1:** Telegram / WhatsApp Web push for new tasks & approvals.
- **P2:** PWA install banner + push notifications.
- **P2:** Multi-currency support, multi-language (Urdu).
- **P2:** Anti-fraud (IP + device fingerprint, duplicate-account detection).
- **P2:** Affiliate share-cards (auto-generated images for WhatsApp).

## Next Action Items
1. Replace seed admin password (`password`) with a strong one after first login.
2. Set up cron: `0 3 * * 0 php /path/dynova/cron/weekly_salary.php`.
3. Edit `app/config.php` DB credentials before deploying to cPanel.
4. Re-test all flows on production once payment-method numbers are real.
