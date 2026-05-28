# DYNOVA NETWORK — PRD (iteration 3 – task packages + brand cleanup)

## Original problem statement (this iteration)
1. Add **Task Packages** (Starter / Silver / Gold / Platinum / Diamond) with price, daily tasks, daily earning and monthly earning. Beautiful cards for users, full CRUD for admin.
2. Remove the "Good Afternoon, <name>" greeting from desktop AND mobile dashboards.
3. On mobile, replace it with a **beautiful alphabetic logo** in a sticky header. Keep main DYNOVA logo as-is.
4. Use the user-provided **JazzCash and EasyPaisa logo PNGs** on the landing page payments section and on deposit/withdraw screens.
5. Remove the word **"Pakistan"** from the homepage / footer / anywhere it appears.

## What was delivered
### Task Packages (DB + admin CRUD + user view)
- New tables `task_packages` and `user_packages` (also bundled as
  `sql/migration_001_packages.sql` – idempotent, safe to re-run on cPanel).
- 5 seeded packages: Starter Rs 500 (5 tasks / Rs 35 daily), Silver Rs 2000 (10/70),
  **Gold Rs 5000 (21 tasks / Rs 147 daily → Rs 4,410 monthly) [matches user spec exactly]**,
  Platinum Rs 10000 (35/280), Diamond Rs 25000 (60/600). All 30-day validity.
- `models/TaskPackage.php` with CRUD + `activate(user, pkg)` that debits balance,
  logs a transaction, and writes a `user_packages` row.
- `controllers/PackageController.php` – user page at `/api/?r=packages`.
- `controllers/AdminController::packages()` – admin CRUD at `/api/?r=admin/packages`.
- `views/user/packages.php` – stunning gradient cards per tier (bronze→diamond colour
  variants), single-column stat list, ROI badge, Activate / Upgrade button, and
  an "Active package" hero banner when one is in use.
- `views/admin/packages.php` – Add form + table with Edit / Toggle / Delete + "★ featured" badge.

### Brand / header cleanup
- Removed every `"Good Morning/Afternoon/Evening, <name>"` greeting.
- Dashboard topbar replaced with a clean `<h2>Dashboard</h2>` + page-sub.
- Created a sticky **mobile header** (`< 1024px`) with:
  - Animated alphabetic **gradient pill** showing the user's first initial (D, S, A…),
    a pulsing conic glow and a green online dot.
  - DYNOVA wordmark + small logo in the centre.
  - Bell on the right.
- Hidden on desktop (sidebar already has the brand).
- Hid the duplicate `.topbar .bell` on mobile so there is only one bell.
- New bottom-nav has 5 items: Home / Tasks / **Plans** / Wallet / Profile
  (and matching admin sidebar entry).

### Payment branding (real PNGs from user)
- Saved user-supplied PNGs into `public/assets/img/jazzcash.png` and
  `public/assets/img/easypaisa.png`.
- New helper `app/payment_logo.php` → `payment_logo_html($name, $size)` returns a
  white rounded "brand pill" with the correct PNG inside. Falls back to the
  letter pill if the brand isn't recognised. Wired into:
  - Landing page (replacing the old "J" / "E" letter blocks)
  - User **deposit** wizard (method picker + payment-detail card)
  - User **withdraw** form (now uses the same `.pm-card` picker, no more
    `<select>` dropdown).

### "Pakistan" scrub
- Grep-verified zero matches in `dynova/app/views/**` and `dynova/public/**`.
- Updated copy: hero pill, hero sub, FAQ, footer tag, footer base, meta description.

### Misc
- **Fixed broken bcrypt hash** for admin@dynova.com in `sql/schema.sql`
  (the original hash never matched "password"). Hash rotated in both the live DB
  and the schema file so fresh cPanel imports work straight away.

## Tech stack (unchanged from previous iteration)
- PHP 8.2 + MariaDB 10.11, supervisor-managed (php_app + mariadb).
- React frontend at `/` redirects → `/api/` (PHP app mounted under `/api`).

## Test credentials
- Admin: `admin@dynova.com` / `password`
- Test user (created during testing): WhatsApp `03009876543`, password `secret123`

## Live preview
https://video-income-24.preview.emergentagent.com/

## Files added / changed this iteration
NEW
- `dynova/sql/migration_001_packages.sql`
- `dynova/app/models/TaskPackage.php`
- `dynova/app/controllers/PackageController.php`
- `dynova/app/payment_logo.php`
- `dynova/app/views/user/packages.php`
- `dynova/app/views/admin/packages.php`
- `dynova/public/assets/img/jazzcash.png`
- `dynova/public/assets/img/easypaisa.png`
- `dynova/public/assets/css/extras.css`

CHANGED
- `dynova/app/bootstrap.php` — pull in payment_logo.php
- `dynova/cron/_bootstrap.php` — same
- `dynova/public/index.php` — `/packages` and `/admin/packages` routes
- `dynova/app/controllers/AdminController.php` — `packages()` action
- `dynova/app/views/layouts/app.php` — sticky mobile header, sidebar Packages link, 5-cell bottom nav
- `dynova/app/views/layouts/admin.php` — Packages sidebar entry + extras.css
- `dynova/app/views/user/dashboard.php` — removed greeting, added .page-head
- `dynova/app/views/user/deposit.php` — real brand logos in pm-card + pm-detail
- `dynova/app/views/user/withdraw.php` — pm-card grid replaces dropdown
- `dynova/app/views/home/landing.php` — brand logos, copy scrub
- `dynova/app/views/layouts/landing.php` — copy scrub
- `dynova/public/assets/css/landing.css` — `.pay-brand` rules
- `dynova/sql/schema.sql` — fixed admin bcrypt hash

## Backlog / not yet implemented
- The package's `daily_tasks` value isn't yet wired into the per-user
  daily-task limit (today still uses the global `daily_task_limit` setting).
  Next iteration: in `Task::completedTodayCount`/`TaskController` use
  `TaskPackage::activeForUser($uid)['daily_tasks']` when present.
- Likewise `daily_earning` per package isn't enforced — task rewards are still
  the per-task field. Next iteration: scale task reward to the package's
  per-day target, or fail the activation if it doesn't fit.
- Package "renew" button when expired.
- A potential `weekly_bonus` cron tweak to expire `user_packages` automatically.
