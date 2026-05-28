# DYNOVA NETWORK

A complete, production-ready video-rating earning platform written in **vanilla PHP 8 + MySQL/MariaDB**, designed to feel like a mobile app. Currency: **PKR**.

## Features

### User
- Signup with WhatsApp number + password + captcha (referral link supported)
- Login with "Remember me" (30 days)
- Mobile-first dashboard with balance, today's earnings, referrals, pending withdrawal
- Bottom navigation (Home / Tasks / Refer / Wallet / Profile)
- **Video rating tasks** — one at a time, 1-5 stars, instant reward to wallet
- Daily task limit (admin-configurable)
- **Deposit** via JazzCash / EasyPesa (manual, admin-approved)
- **Withdraw** to JazzCash / EasyPesa (manual, admin-approved)
- **3-Level referrals** (10% / 5% / 2.5% — all percentages admin-editable)
- Referral page with daily/weekly/yearly toggle, Team A/B/C breakdown
- **Salary system** — auto rank promotion + weekly salary cron
- Transaction history with full ledger
- Profile, change password, support shortcut

### Admin
- Separate secure login
- KPI dashboard + 7-day earnings chart
- Manage users (search, view, block, balance adjust)
- Approve / reject deposits & withdrawals
- Add / edit / toggle / delete video tasks
- View any user's 3-level referral tree
- **Edit referral commission percentages**
- **Edit payment methods** (add / edit / delete JazzCash / EasyPesa / others)
- **Edit salary ranks** (min refs, min business volume, weekly amount)
- Trigger weekly salary payout manually
- Filterable transaction ledger

### Security
- PDO prepared statements everywhere
- `password_hash()` (bcrypt) for users & admins
- CSRF tokens on every POST form
- `htmlspecialchars()` on every output
- HttpOnly + SameSite cookies, secure session naming
- Remember-me uses SHA-256 of a server-side token

---

## Folder structure

```
dynova/
├── app/
│   ├── bootstrap.php          # session + autoload + helpers
│   ├── config.php             # DB credentials + business defaults
│   ├── db.php                 # PDO singleton
│   ├── auth.php               # current_user / require_admin etc.
│   ├── helpers.php            # url(), csrf(), e(), money(), settings
│   ├── controllers/           # Auth, Dashboard, Task, Referral, Wallet, Profile, Admin
│   ├── models/                # User, Task, Deposit, Withdrawal, Referral, Salary, Transaction, PaymentMethod
│   └── views/
│       ├── layouts/           # app, auth, admin
│       ├── auth/              # login, signup
│       ├── user/              # dashboard, tasks, referrals, wallet, deposit, withdraw, profile, change_password
│       ├── admin/             # login, dashboard, users, deposits, withdrawals, tasks, referrals, settings, ranks, transactions, user_edit
│       └── errors/            # 404
├── public/                    # Web root
│   ├── index.php              # Front controller
│   ├── router.php             # Router for `php -S`
│   ├── .htaccess              # Apache rules
│   └── assets/ (css / js / img)
├── cron/weekly_salary.php     # Run every Sunday
└── sql/schema.sql             # Complete schema + seed
```

---

## Run on cPanel / Apache / shared hosting

1. Create a MySQL database via cPanel; note the host, name, user, password.
2. Import `sql/schema.sql` via phpMyAdmin.
3. Upload the contents of the project so that **`public/` is your web-root** (usually `public_html/`).
   - Move `app/`, `cron/`, `sql/` **one level above** `public_html/` for safety.
   - Edit `public/index.php` so the bootstrap path matches (currently `../app/bootstrap.php`).
4. Edit `app/config.php` with your DB credentials. **Set `BASE_URL` to `""`** (empty string).
5. Visit `https://yourdomain.com/` — login page should load.
6. Login as admin → email `admin@dynova.com`, password `password` (change it immediately from Profile > Change Password after signing in via the same email, OR run the SQL update below).
7. Set up cron job (cPanel → Cron Jobs):
   ```
   0 3 * * 0   /usr/bin/php /home/<user>/dynova/cron/weekly_salary.php
   ```
   (Runs every Sunday 03:00 — pays weekly salary to all eligible users.)

### Update admin password via SQL
```sql
UPDATE admins SET password_hash = '$2y$10$REPLACE_WITH_NEW_BCRYPT' WHERE email='admin@dynova.com';
```
Generate a new hash via:
```bash
php -r "echo password_hash('YourNewPass', PASSWORD_BCRYPT);"
```

---

## Run locally (XAMPP / WAMP / MAMP / native PHP)

```bash
# 1. Import schema
mysql -u root -p < sql/schema.sql

# 2. Edit app/config.php (DB creds, set BASE_URL = "")

# 3. Start PHP built-in server
php -S 0.0.0.0:8080 -t public public/router.php

# 4. Open http://localhost:8080
```

---

## Run on Emergent preview

The app runs behind a FastAPI reverse proxy under `/api/`:
- App entry: `/api/?r=auth/login`
- Admin entry: `/api/?r=admin/login`

This is already pre-configured (`BASE_URL=/api` in `app/config.php`) and managed by supervisor (`mariadb` + `php` programs). The React frontend at `/` simply redirects to `/api/`.

---

## Database tables (created by `sql/schema.sql`)
- `users`, `admins`, `admin_settings`, `payment_methods`
- `tasks`, `task_completions`
- `deposits`, `withdrawals`
- `referrals` (commission ledger), `salary_ranks`, `salaries`
- `transactions` (full money ledger)

All amounts are `DECIMAL(12,2)`. All foreign keys are explicit. Engine is InnoDB.

---

## License & support
Built for a single client. Use freely. Modify freely.
