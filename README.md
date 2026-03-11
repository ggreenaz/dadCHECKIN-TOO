# dadCHECKIN-TOO v2

**Visitor Management System** — A modern, web-based check-in system for schools and offices.
Built with PHP 8.1+, MySQL 8, and Apache. No frameworks. No composer. Just drop it in and run the wizard.

---

## What It Does

- **Visitor check-in kiosk** — touchscreen-friendly form at `/checkin`
- **Visitor check-out** — fast phone/name lookup at `/depart`
- **Live board** — real-time display for the main office at `/board`
- **Admin dashboard** — live stats, visit history, analytics, and reporting
- **Email notifications** — automatic alerts when specific visitors arrive
- **LDAP / Active Directory login** — staff use their existing network credentials
- **Google & Microsoft SSO** — single sign-on via OAuth
- **Automatic end-of-day checkout** — cron-based, prevents ghost visitors
- **Guided upgrade** from the original dadtoo v1 database

---

## Requirements

| Requirement | Minimum |
|---|---|
| PHP | 8.1+ |
| MySQL / MariaDB | 8.0+ / 10.4+ |
| Apache | 2.4+ with `mod_rewrite` |
| PHP Extensions | `pdo_mysql`, `mbstring`, `curl`, `json`, `openssl`, `session`, `ldap` (for LDAP auth) |

---

## Quick Install

```bash
# 1. Clone into your web root
git clone https://github.com/ggreenaz/dadcheckin-too.git /var/www/checkin

# 2. Set permissions
chown -R www-data:www-data /var/www/checkin
chmod -R 755 /var/www/checkin
chmod 664 /var/www/checkin/config/database.php

# 3. Point Apache at the public/ directory (see docs/install-guide for full vhost config)

# 4. Visit http://yourdomain.com/install in your browser
#    The wizard will guide you through the rest.
```

---

## Upgrading from dadtoo v1

Run these commands **inside your existing dadtoo directory** — the new code installs on top, your `config.php` stays in place, and the wizard reads your database credentials automatically:

```bash
cd /var/www/dadtoo        # your existing dadtoo directory
git init
git remote add origin https://github.com/ggreenaz/dadCHECKIN-TOO.git
git fetch origin
git checkout -f master
chown -R www-data:www-data .
chmod -R 755 .
chmod 775 config
```

Then visit `http://yourdomain.com/install` — the wizard detects your existing dadtoo database and walks you through the **Guided Upgrade**. No re-entering of credentials required.

---

## Configuration Files

| File | Purpose |
|---|---|
| `config/app.php` | Application name, URL, timezone, session settings |
| `config/database.php` | Database connection defaults |
| `config/database.local.php` | **Your local credentials** — created by the install wizard, never committed |
| `config/installed.lock` | Created after install completes — delete to re-run the wizard |

---

## Directory Structure

```
/
├── app/
│   ├── Controllers/     # Route handlers
│   ├── Core/            # Router, View, Auth, Database
│   ├── Models/          # Data access layer
│   ├── Auth/            # Authentication providers (Local, LDAP, Google, Microsoft)
│   └── Views/           # PHP templates
├── config/              # Configuration files
├── database/
│   ├── schema.sql       # Full database schema
│   └── migrations/      # Schema migration scripts
├── public/              # Web root (point Apache DocumentRoot here)
│   ├── index.php        # Front controller
│   ├── .htaccess        # Rewrite rules
│   └── css/             # Stylesheets
├── routes/
│   └── web.php          # All application routes
└── scripts/
    └── auto_checkout.php  # Cron script for automatic end-of-day checkout
```

---

## Auto-Checkout Cron

To automatically close visits at end of day, add this to your server's crontab:

```
*/15 * * * * php /var/www/checkin/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1
```

---

## Documentation

Full documentation is built into the application at `/admin/docs` after installation, including:

- **Configuration Guide** — every setting explained
- **End User Guide** — printable staff handbook (save as PDF from `/admin/docs/userguide`)

---

## License

MIT License — free to use, modify, and distribute.

---

*dadCHECKIN-TOO v2 — Built for schools. Works for everyone.*
