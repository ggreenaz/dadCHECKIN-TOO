<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>dadCHECKIN-TOO — Installation Guide</title>
<style>
/* ── RESET & BASE ────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
html { font-size: 14px; }
body {
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Arial, sans-serif;
    color: #1a1a2e;
    background: #fff;
    line-height: 1.65;
    max-width: 820px;
    margin: 0 auto;
    padding: 40px 32px 80px;
}
h1 { font-size: 2rem;    font-weight: 800; color: #1a1a2e; }
h2 { font-size: 1.25rem; font-weight: 700; color: #1a1a2e; margin: 0 0 10px; }
h3 { font-size: 1rem;    font-weight: 700; color: #1a1a2e; margin: 20px 0 6px; }
p  { margin: 0 0 12px; }
ul, ol { margin: 8px 0 14px 22px; }
li { margin-bottom: 5px; }
a  { color: #4f46e5; }
code {
    font-family: 'SFMono-Regular', Consolas, 'Courier New', monospace;
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 4px;
    padding: 1px 5px;
    font-size: 0.88em;
}
pre {
    background: #0f172a;
    color: #e2e8f0;
    border-radius: 8px;
    padding: 16px 20px;
    overflow-x: auto;
    font-family: 'SFMono-Regular', Consolas, 'Courier New', monospace;
    font-size: 0.88em;
    line-height: 1.6;
    margin: 10px 0 18px;
}
pre code {
    background: none;
    border: none;
    padding: 0;
    font-size: 1em;
    color: inherit;
}
strong { font-weight: 700; }

/* ── SCREEN-ONLY CONTROLS ───────────────────────────────────── */
.screen-only {
    position: fixed;
    top: 16px; right: 16px;
    display: flex; gap: 10px;
    z-index: 999;
}
.btn-print {
    background: #4f46e5;
    color: #fff;
    border: none;
    border-radius: 8px;
    padding: 10px 20px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    box-shadow: 0 2px 8px rgba(79,70,229,.3);
}
.btn-back {
    background: #fff;
    color: #4f46e5;
    border: 1.5px solid #4f46e5;
    border-radius: 8px;
    padding: 9px 18px;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
}

/* ── COVER ──────────────────────────────────────────────────── */
.cover {
    text-align: center;
    padding: 60px 0 50px;
    border-bottom: 3px solid #4f46e5;
    margin-bottom: 48px;
}
.cover-logo { font-size: 3.5rem; margin-bottom: 16px; }
.cover h1   { font-size: 2.4rem; margin-bottom: 10px; letter-spacing: -.02em; }
.cover-sub  { font-size: 1.1rem; color: #64748b; margin-bottom: 6px; }
.cover-ver  { font-size: 0.85rem; color: #94a3b8; }

/* ── TOC ────────────────────────────────────────────────────── */
.toc {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 24px 28px;
    margin-bottom: 48px;
}
.toc h2 { font-size: 1rem; color: #64748b; margin-bottom: 14px; letter-spacing: .05em; text-transform: uppercase; }
.toc ol { margin: 0; padding-left: 20px; columns: 2; column-gap: 32px; }
.toc li { font-size: 0.9rem; margin-bottom: 6px; break-inside: avoid; }
.toc a  { color: #4f46e5; text-decoration: none; font-weight: 500; }
.toc a:hover { text-decoration: underline; }

/* ── SECTION ────────────────────────────────────────────────── */
.section {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-left: 4px solid #4f46e5;
    border-radius: 0 10px 10px 0;
    padding: 24px 28px;
    margin-bottom: 32px;
}
.section-num {
    display: inline-block;
    background: #4f46e5;
    color: #fff;
    font-size: 0.75rem;
    font-weight: 700;
    border-radius: 4px;
    padding: 2px 8px;
    margin-bottom: 8px;
    letter-spacing: .05em;
}

/* ── CALLOUT BOXES ──────────────────────────────────────────── */
.callout {
    border-radius: 8px;
    padding: 14px 18px;
    margin: 14px 0;
    font-size: 0.92rem;
}
.callout-tip  { background: #eff6ff; border: 1px solid #bfdbfe; }
.callout-warn { background: #fffbeb; border: 1px solid #fcd34d; }
.callout-info { background: #f0fdf4; border: 1px solid #bbf7d0; }
.callout-label { font-weight: 700; margin-right: 4px; }

/* ── REQUIREMENTS TABLE ─────────────────────────────────────── */
table {
    width: 100%;
    border-collapse: collapse;
    margin: 12px 0 18px;
    font-size: 0.92rem;
}
th {
    background: #1a1a2e;
    color: #fff;
    text-align: left;
    padding: 8px 12px;
    font-weight: 600;
}
td {
    padding: 8px 12px;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: top;
}
tr:last-child td { border-bottom: none; }
tr:nth-child(even) td { background: #f8fafc; }

/* ── STEP BADGES ────────────────────────────────────────────── */
.step-list { list-style: none; margin: 0; padding: 0; }
.step-list li {
    display: flex;
    gap: 16px;
    margin-bottom: 16px;
    align-items: flex-start;
}
.step-badge {
    flex-shrink: 0;
    width: 28px; height: 28px;
    background: #4f46e5;
    color: #fff;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.8rem;
    font-weight: 700;
}
.step-body { flex: 1; padding-top: 3px; }

/* ── CHECK LISTS ────────────────────────────────────────────── */
.check-list { list-style: none; margin: 8px 0 14px 0; padding: 0; }
.check-list li { padding-left: 24px; position: relative; margin-bottom: 6px; }
.check-list li::before { content: '✓'; position: absolute; left: 0; color: #16a34a; font-weight: 700; }

/* ── QUICK REF CARD ─────────────────────────────────────────── */
.qrc {
    background: #1a1a2e;
    color: #e2e8f0;
    border-radius: 10px;
    padding: 24px 28px;
    margin-top: 8px;
}
.qrc h2 { color: #fff; margin-bottom: 16px; }
.qrc table { font-size: 0.88rem; }
.qrc th { background: #2d2d52; }
.qrc td { border-color: #2d2d52; color: #cbd5e1; }
.qrc tr:nth-child(even) td { background: #1e1e3f; }
.qrc code { background: #2d2d52; border-color: #3d3d6e; color: #a5b4fc; }

/* ── PRINT ──────────────────────────────────────────────────── */
@media print {
    body { padding: 0; font-size: 11pt; max-width: 100%; }
    .screen-only { display: none !important; }
    .cover { padding: 40px 0 36px; }
    .cover h1 { font-size: 2rem; }
    .section { break-inside: avoid; }
    pre { background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; }
    pre code { color: #0f172a; }
    a { color: #4f46e5; text-decoration: none; }
    .toc { break-after: page; }
    h2, h3 { break-after: avoid; }
    .qrc { background: #f8fafc; color: #1a1a2e; break-inside: avoid; }
    .qrc h2 { color: #1a1a2e; }
    .qrc th { background: #e2e8f0; color: #1a1a2e; }
    .qrc td { color: #1a1a2e; }
    .qrc tr:nth-child(even) td { background: #f8fafc; }
    .qrc code { background: #e2e8f0; color: #1a1a2e; }
}
</style>
</head>
<body>

<!-- Screen controls -->
<div class="screen-only">
    <a href="/admin/docs" class="btn-back">← Back to Docs</a>
    <button class="btn-print" onclick="window.print()">🖨 Print / Save PDF</button>
</div>

<!-- Cover -->
<div class="cover">
    <div class="cover-logo">📋</div>
    <h1>dadCHECKIN-TOO</h1>
    <div class="cover-sub">Installation &amp; Upgrade Guide</div>
    <div class="cover-sub">For system administrators and IT staff</div>
    <div class="cover-ver">Version 2.0 &mdash; <?= date('F Y') ?></div>
</div>

<!-- TOC -->
<div class="toc">
    <h2>Table of Contents</h2>
    <ol>
        <li><a href="#requirements">System Requirements</a></li>
        <li><a href="#before-start">Before You Begin</a></li>
        <li><a href="#fresh-install">Fresh Installation</a></li>
        <li><a href="#upgrade">Upgrading from dadtoo v1</a></li>
        <li><a href="#apache">Apache Virtual Host</a></li>
        <li><a href="#database">Database Setup</a></li>
        <li><a href="#wizard">Running the Install Wizard</a></li>
        <li><a href="#cron">Cron Job (Auto-Checkout)</a></li>
        <li><a href="#permissions">File Permissions</a></li>
        <li><a href="#ldap">LDAP / Active Directory</a></li>
        <li><a href="#sso">Google &amp; Microsoft SSO</a></li>
        <li><a href="#verify">Post-Install Verification</a></li>
        <li><a href="#troubleshoot">Troubleshooting</a></li>
        <li><a href="#quickref">Quick Reference Card</a></li>
    </ol>
</div>

<!-- ── 1. Requirements ────────────────────────────────────────── -->
<div class="section" id="requirements">
    <div class="section-num">SECTION 1</div>
    <h2>System Requirements</h2>
    <p>dadCHECKIN-TOO runs on any standard LAMP stack. The table below lists the minimum versions required.</p>

    <table>
        <tr><th>Component</th><th>Minimum Version</th><th>Notes</th></tr>
        <tr><td><strong>PHP</strong></td><td>8.1+</td><td>8.2 or 8.3 recommended</td></tr>
        <tr><td><strong>MySQL</strong></td><td>8.0+</td><td>MariaDB 10.4+ also works</td></tr>
        <tr><td><strong>Apache</strong></td><td>2.4+</td><td><code>mod_rewrite</code> must be enabled</td></tr>
        <tr><td><strong>Git</strong></td><td>2.x</td><td>For installation via <code>git clone</code></td></tr>
        <tr><td><strong>Disk space</strong></td><td>50 MB</td><td>Plus database storage</td></tr>
        <tr><td><strong>RAM</strong></td><td>512 MB</td><td>1 GB+ for busy sites</td></tr>
    </table>

    <h3>Required PHP Extensions</h3>
    <table>
        <tr><th>Extension</th><th>Required?</th><th>Purpose</th></tr>
        <tr><td><code>pdo_mysql</code></td><td>Required</td><td>Database connectivity</td></tr>
        <tr><td><code>mbstring</code></td><td>Required</td><td>Multi-byte string handling</td></tr>
        <tr><td><code>curl</code></td><td>Required</td><td>OAuth SSO callbacks</td></tr>
        <tr><td><code>json</code></td><td>Required</td><td>API responses and settings storage</td></tr>
        <tr><td><code>openssl</code></td><td>Required</td><td>HTTPS and token encryption</td></tr>
        <tr><td><code>session</code></td><td>Required</td><td>User sessions</td></tr>
        <tr><td><code>ldap</code></td><td>Optional</td><td>LDAP / Active Directory login only</td></tr>
    </table>

    <div class="callout callout-tip">
        <span class="callout-label">Tip:</span>
        To check which extensions are loaded, run <code>php -m</code> on the command line or create a temporary <code>phpinfo.php</code> page.
    </div>
</div>

<!-- ── 2. Before You Begin ────────────────────────────────────── -->
<div class="section" id="before-start">
    <div class="section-num">SECTION 2</div>
    <h2>Before You Begin</h2>
    <p>Gather this information before starting. The install wizard will ask for it.</p>

    <h3>What you will need</h3>
    <ul class="check-list">
        <li>MySQL credentials: hostname, port, database name, username, password</li>
        <li>The domain name or IP address for the application (e.g. <code>checkin.myschool.org</code>)</li>
        <li>Your organization's name and time zone</li>
        <li>An email address for the first administrator account</li>
        <li>SMTP server details if you want email notifications (optional)</li>
        <li>Your LDAP / Active Directory server address and bind credentials (optional)</li>
        <li>Google or Microsoft OAuth client ID and secret (optional, for SSO)</li>
    </ul>

    <h3>Upgrading from dadtoo v1?</h3>
    <p>Good news — you don't need to gather database credentials. The upgrade wizard reads them directly from your existing <code>config.php</code>. All you need is:</p>
    <ul class="check-list">
        <li>SSH access to your server</li>
        <li>Your organization name and time zone (to confirm during the wizard)</li>
        <li>A database backup (strongly recommended before any upgrade)</li>
    </ul>

    <div class="callout callout-warn">
        <span class="callout-label">Important:</span>
        Always take a full backup of your existing dadtoo v1 database before running the upgrade. The upgrade wizard reads but does not modify the old database; however, backups are strongly recommended.
    </div>
</div>

<!-- ── 3. Fresh Installation ──────────────────────────────────── -->
<div class="section" id="fresh-install">
    <div class="section-num">SECTION 3</div>
    <h2>Fresh Installation</h2>
    <p>Use this path if you are installing for the first time on a server with no existing dadtoo data.</p>

    <ol class="step-list">
        <li>
            <div class="step-badge">1</div>
            <div class="step-body">
                <strong>SSH into your server</strong> and navigate to your web directory.
                <pre><code>ssh user@yourserver.org</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">2</div>
            <div class="step-body">
                <strong>Clone the repository</strong> directly into the target directory.
<pre><code>git clone https://github.com/ggreenaz/dadCHECKIN-TOO.git /var/www/checkin</code></pre>
                <div class="callout callout-tip">
                    <span class="callout-label">Tip:</span>
                    The dot at the end of the <code>git clone</code> command clones into the current directory rather than creating a subdirectory. Make sure the target folder is empty before cloning.
                </div>
            </div>
        </li>
        <li>
            <div class="step-badge">3</div>
            <div class="step-body">
                <strong>Set file ownership</strong> so Apache can read the files.
<pre><code>chown -R www-data:www-data /var/www/checkin
chmod -R 755 /var/www/checkin</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">4</div>
            <div class="step-body">
                <strong>Create an Apache virtual host</strong> pointing to the <code>public/</code> subdirectory. See <a href="#apache">Section 5 — Apache Virtual Host</a> for the full configuration.
            </div>
        </li>
        <li>
            <div class="step-badge">5</div>
            <div class="step-body">
                <strong>Create a MySQL database</strong> for the application. See <a href="#database">Section 6 — Database Setup</a>.
            </div>
        </li>
        <li>
            <div class="step-badge">6</div>
            <div class="step-body">
                <strong>Open the install wizard</strong> in your browser.
<pre><code>http://checkin.myschool.org/install</code></pre>
                Follow the on-screen steps. The wizard will write your configuration files and create all database tables automatically.
            </div>
        </li>
    </ol>
</div>

<!-- ── 4. Upgrading from dadtoo v1 ───────────────────────────── -->
<div class="section" id="upgrade">
    <div class="section-num">SECTION 4</div>
    <h2>Upgrading from dadtoo v1</h2>
    <p>
        The upgrade is designed to install <strong>on top of your existing dadtoo directory</strong>. The new v2 code lands in the same folder your old installation is already in. Your existing <code>config.php</code> stays in place — the upgrade wizard reads your database credentials from it automatically. You do not need to re-enter anything.
    </p>

    <div class="callout callout-warn">
        <span class="callout-label">Back up first:</span>
        Always back up your database before upgrading.
        <code>mysqldump -u root -p dadtoo &gt; dadtoo_backup_$(date +%Y%m%d).sql</code>
    </div>

    <ol class="step-list">
        <li>
            <div class="step-badge">1</div>
            <div class="step-body">
                <strong>Go into your existing dadtoo directory</strong> (wherever Apache currently points).
<pre><code>cd /var/www/dadtoo</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">2</div>
            <div class="step-body">
                <strong>Pull the v2 code on top of the existing installation.</strong> This leaves your <code>config.php</code> in place and adds all new files.
<pre><code>git init
git remote add origin https://github.com/ggreenaz/dadCHECKIN-TOO.git
git fetch origin
git checkout -f master</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">3</div>
            <div class="step-body">
                <strong>Set permissions</strong> so Apache can read the new files.
<pre><code>chown -R www-data:www-data /var/www/dadtoo
chmod -R 755 /var/www/dadtoo
chmod 775 /var/www/dadtoo/config</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">4</div>
            <div class="step-body">
                <strong>Update your Apache virtual host</strong> to point to the <code>public/</code> subdirectory.
<pre><code>DocumentRoot /var/www/dadtoo/public</code></pre>
                Reload Apache after saving the change:
<pre><code>systemctl reload apache2</code></pre>
            </div>
        </li>
        <li>
            <div class="step-badge">5</div>
            <div class="step-body">
                <strong>Visit <code>/install</code> in your browser.</strong>
                <p>The wizard detects your existing <code>config.php</code>, reads your database credentials automatically, and routes you straight into the <strong>Guided Upgrade</strong> — no re-typing of connection details required.</p>
            </div>
        </li>
        <li>
            <div class="step-badge">6</div>
            <div class="step-body">
                <strong>Follow the Guided Upgrade steps:</strong>
                <ol>
                    <li>Confirm organization name and time zone</li>
                    <li>Set up departments (new in v2)</li>
                    <li>Configure authentication (local, LDAP, or SSO)</li>
                    <li>Configure kiosk fields and appearance</li>
                    <li>Set up notifications (optional)</li>
                    <li>Run the migration — all data streams in real time</li>
                    <li>Review the summary and finish</li>
                </ol>
            </div>
        </li>
        <li>
            <div class="step-badge">7</div>
            <div class="step-body">
                <strong>Verify the migration</strong> by logging in and checking your visit history, hosts, and reasons.
            </div>
        </li>
    </ol>

    <div class="callout callout-info">
        <span class="callout-label">What gets migrated:</span>
        All visitor records, visit history, host names, visit reasons, and organization settings are migrated automatically. Admin user accounts are not migrated from v1 — you will create a fresh admin account during the wizard.
    </div>

    <div class="callout callout-tip">
        <span class="callout-label">Future updates:</span>
        Once v2 is installed, updating to a newer release is simply:
        <code>cd /var/www/dadtoo &amp;&amp; git pull</code>
    </div>
</div>

<!-- ── 5. Apache Virtual Host ─────────────────────────────────── -->
<div class="section" id="apache">
    <div class="section-num">SECTION 5</div>
    <h2>Apache Virtual Host Configuration</h2>
    <p>
        The Apache <code>DocumentRoot</code> must point to the <code>public/</code> subdirectory inside the installation, not the root of the repository. This keeps all application code outside the web root.
    </p>

    <h3>Basic HTTP Virtual Host</h3>
<pre><code>&lt;VirtualHost *:80&gt;
    ServerName checkin.myschool.org
    DocumentRoot /var/www/checkin/public

    &lt;Directory /var/www/checkin/public&gt;
        AllowOverride All
        Require all granted
    &lt;/Directory&gt;

    ErrorLog  ${APACHE_LOG_DIR}/checkin_error.log
    CustomLog ${APACHE_LOG_DIR}/checkin_access.log combined
&lt;/VirtualHost&gt;</code></pre>

    <h3>HTTPS Virtual Host (recommended)</h3>
<pre><code>&lt;VirtualHost *:443&gt;
    ServerName checkin.myschool.org
    DocumentRoot /var/www/checkin/public

    SSLEngine on
    SSLCertificateFile    /etc/ssl/certs/checkin.crt
    SSLCertificateKeyFile /etc/ssl/private/checkin.key

    &lt;Directory /var/www/checkin/public&gt;
        AllowOverride All
        Require all granted
    &lt;/Directory&gt;

    ErrorLog  ${APACHE_LOG_DIR}/checkin_error.log
    CustomLog ${APACHE_LOG_DIR}/checkin_access.log combined
&lt;/VirtualHost&gt;

&lt;!-- Redirect HTTP to HTTPS --&gt;
&lt;VirtualHost *:80&gt;
    ServerName checkin.myschool.org
    Redirect permanent / https://checkin.myschool.org/
&lt;/VirtualHost&gt;</code></pre>

    <h3>Enable the site and reload</h3>
<pre><code># Save the config to /etc/apache2/sites-available/checkin.conf
a2ensite checkin.conf
a2enmod rewrite
systemctl reload apache2</code></pre>

    <div class="callout callout-warn">
        <span class="callout-label">Important:</span>
        <code>AllowOverride All</code> is required. Without it, the <code>.htaccess</code> rewrite rules in <code>public/</code> will not work and every page will return a 404.
    </div>
</div>

<!-- ── 6. Database Setup ───────────────────────────────────────── -->
<div class="section" id="database">
    <div class="section-num">SECTION 6</div>
    <h2>Database Setup</h2>
    <p>Create a dedicated MySQL database and user for the application before running the install wizard. The wizard will create all tables automatically — you do not need to import any SQL files manually.</p>

    <h3>Create the database and user</h3>
<pre><code>mysql -u root -p

-- Inside the MySQL prompt:
CREATE DATABASE checkin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'checkin_user'@'localhost' IDENTIFIED BY 'your_strong_password_here';
GRANT ALL PRIVILEGES ON checkin.* TO 'checkin_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;</code></pre>

    <div class="callout callout-tip">
        <span class="callout-label">Tip:</span>
        Use a unique, strong password for the database user. This password will be stored in <code>config/database.local.php</code> on the server and is never committed to git.
    </div>

    <h3>Database parameters you will need for the wizard</h3>
    <table>
        <tr><th>Field</th><th>Typical Value</th></tr>
        <tr><td>Host</td><td><code>localhost</code> (or your DB server IP)</td></tr>
        <tr><td>Port</td><td><code>3306</code></td></tr>
        <tr><td>Database name</td><td><code>checkin</code> (whatever you created above)</td></tr>
        <tr><td>Username</td><td><code>checkin_user</code></td></tr>
        <tr><td>Password</td><td>the password you set above</td></tr>
    </table>
</div>

<!-- ── 7. Running the Install Wizard ─────────────────────────── -->
<div class="section" id="wizard">
    <div class="section-num">SECTION 7</div>
    <h2>Running the Install Wizard</h2>
    <p>The install wizard is a browser-based step-by-step setup tool. Navigate to <code>/install</code> on your server to begin.</p>

    <h3>Wizard steps (fresh install path)</h3>
    <ol>
        <li><strong>Choose path</strong> — Fresh install or upgrade from dadtoo v1</li>
        <li><strong>Requirements check</strong> — The wizard verifies your PHP extensions and file permissions</li>
        <li><strong>Database connection</strong> — Enter your MySQL credentials and test the connection</li>
        <li><strong>Organization setup</strong> — Name, URL, time zone, and language</li>
        <li><strong>Admin account</strong> — Create the first super-admin login</li>
        <li><strong>Finish</strong> — Tables are created, config files are written, and the lock file is placed</li>
    </ol>

    <div class="callout callout-info">
        <span class="callout-label">After install:</span>
        The wizard creates <code>config/database.local.php</code> and <code>config/installed.lock</code>. The lock file prevents the wizard from running again. To re-run the wizard, delete the lock file: <code>rm /var/www/checkin/config/installed.lock</code>
    </div>

    <h3>What happens to the /install route after install?</h3>
    <p>
        Once <code>config/installed.lock</code> exists, all <code>/install</code> routes redirect to the dashboard. The install wizard is effectively disabled. If you need to re-run it, delete the lock file first.
    </p>

    <h3>First login</h3>
    <p>After the wizard completes, navigate to <code>/auth/login</code>. Log in with the email and password you set in step 5 of the wizard. You will land on the admin dashboard.</p>
    <p>From there, go to <strong>Admin &rarr; Setup</strong> to complete configuration: add hosts, visit reasons, custom fields, and configure authentication providers.</p>
</div>

<!-- ── 8. Cron Job ────────────────────────────────────────────── -->
<div class="section" id="cron">
    <div class="section-num">SECTION 8</div>
    <h2>Cron Job — Automatic End-of-Day Checkout</h2>
    <p>
        dadCHECKIN-TOO can automatically close any visits that are still open at the end of the school day. This prevents "ghost visitors" from appearing on the board overnight. A cron script handles this.
    </p>

    <h3>Add the cron entry</h3>
    <p>Run <code>crontab -e</code> as root (or the www-data user) and add this line:</p>
<pre><code># Run every 15 minutes — closes stale visits per org auto-checkout settings
*/15 * * * * php /var/www/checkin/scripts/auto_checkout.php >> /var/log/checkin-auto-checkout.log 2>&1</code></pre>

    <div class="callout callout-tip">
        <span class="callout-label">Tip:</span>
        Running every 15 minutes is recommended. The script checks each organization's configured auto-checkout time and only acts after that time has passed.
    </div>

    <h3>Configure auto-checkout time</h3>
    <p>In the admin panel, go to <strong>Settings &rarr; Auto-Checkout</strong> to set:</p>
    <ul>
        <li><strong>Enable auto-checkout</strong> — turn the feature on or off</li>
        <li><strong>Checkout time</strong> — the time of day to close open visits (e.g. 5:00 PM)</li>
        <li><strong>Apply on weekends</strong> — whether to run on Saturdays and Sundays</li>
    </ul>

    <h3>Manual log review</h3>
<pre><code>tail -f /var/log/checkin-auto-checkout.log</code></pre>
    <p>The log shows how many visits were closed each run and any errors encountered.</p>
</div>

<!-- ── 9. File Permissions ────────────────────────────────────── -->
<div class="section" id="permissions">
    <div class="section-num">SECTION 9</div>
    <h2>File Permissions</h2>
    <p>All files must be readable by the Apache user (<code>www-data</code> on Debian/Ubuntu, <code>apache</code> on CentOS/RHEL). The <code>config/</code> directory needs to be writable by the web server so the install wizard can create configuration files.</p>

    <h3>Standard permission setup</h3>
<pre><code># Set ownership
chown -R www-data:www-data /var/www/checkin

# Directories: 755 (rwxr-xr-x)
find /var/www/checkin -type d -exec chmod 755 {} \;

# Files: 644 (rw-r--r--)
find /var/www/checkin -type f -exec chmod 644 {} \;</code></pre>

    <h3>Config directory (writable during install)</h3>
<pre><code># Allow the wizard to write config files
chmod 775 /var/www/checkin/config</code></pre>

    <div class="callout callout-warn">
        <span class="callout-label">Security note:</span>
        After the install wizard completes and you have verified the installation, you can tighten the config directory back to <code>755</code>: <code>chmod 755 /var/www/checkin/config</code>. The application only reads config files during normal operation.
    </div>

    <h3>Summary table</h3>
    <table>
        <tr><th>Path</th><th>Owner</th><th>Permission</th></tr>
        <tr><td><code>/var/www/checkin/</code></td><td><code>www-data</code></td><td><code>755</code></td></tr>
        <tr><td><code>/var/www/checkin/config/</code></td><td><code>www-data</code></td><td><code>775</code> (install), <code>755</code> (after)</td></tr>
        <tr><td><code>/var/www/checkin/public/</code></td><td><code>www-data</code></td><td><code>755</code></td></tr>
        <tr><td>All PHP files</td><td><code>www-data</code></td><td><code>644</code></td></tr>
        <tr><td>All directories</td><td><code>www-data</code></td><td><code>755</code></td></tr>
    </table>
</div>

<!-- ── 10. LDAP / Active Directory ───────────────────────────── -->
<div class="section" id="ldap">
    <div class="section-num">SECTION 10</div>
    <h2>LDAP / Active Directory Setup</h2>
    <p>
        dadCHECKIN-TOO supports logging in with Active Directory or any LDAP-compatible directory. Staff use their existing network usernames and passwords — no separate account management required.
    </p>

    <div class="callout callout-warn">
        <span class="callout-label">Prerequisite:</span>
        The PHP <code>ldap</code> extension must be installed: <code>apt install php-ldap &amp;&amp; systemctl restart apache2</code>
    </div>

    <h3>Configuration (Admin &rarr; Setup &rarr; Authentication)</h3>
    <table>
        <tr><th>Field</th><th>Example</th><th>Notes</th></tr>
        <tr><td>Server Host</td><td><code>ldap://dc1.myschool.org</code></td><td>Use <code>ldaps://</code> for port 636</td></tr>
        <tr><td>Port</td><td><code>389</code></td><td>636 for LDAPS</td></tr>
        <tr><td>Directory Type</td><td>Microsoft Active Directory</td><td>Enables correct subtree search</td></tr>
        <tr><td>Base DN</td><td><code>DC=myschool,DC=org</code></td><td>Top of your domain</td></tr>
        <tr><td>Bind DN</td><td><code>CN=svc-checkin,OU=Service Accounts,DC=myschool,DC=org</code></td><td>Service account with read access</td></tr>
        <tr><td>Bind Password</td><td>&mdash;</td><td>Service account password</td></tr>
        <tr><td>Username Attribute</td><td><code>sAMAccountName</code></td><td>AD username field</td></tr>
        <tr><td>Email Attribute</td><td><code>mail</code></td><td>User email in AD</td></tr>
        <tr><td>Display Name</td><td><code>displayName</code></td><td>Full name shown in the app</td></tr>
        <tr><td>Search Filter</td><td><code>(objectClass=user)</code></td><td>Limits search to user objects</td></tr>
    </table>

    <h3>Testing the connection</h3>
    <p>Use the <strong>Test Connection</strong> button on the Authentication setup page. A green success message means the bind and user search worked. If it fails, check:</p>
    <ul>
        <li>Firewall — TCP port 389 (or 636) must be open from the web server to your domain controller</li>
        <li>Service account — must have read permission on the users OU</li>
        <li>Base DN format — use the DN format, not FQDN (e.g. <code>DC=myschool,DC=org</code> not <code>myschool.org</code>)</li>
    </ul>

    <h3>LDAP Access Mode</h3>
    <p>After configuring LDAP, set the access mode in <strong>Admin &rarr; Users</strong>:</p>
    <ul>
        <li><strong>Mixed mode</strong> — local accounts and LDAP accounts can both log in</li>
        <li><strong>LDAP only</strong> — only directory users can log in (local accounts disabled)</li>
    </ul>
    <div class="callout callout-warn">
        <span class="callout-label">Warning:</span>
        Do not switch to LDAP-only mode until you have verified at least one LDAP account can log in successfully. Otherwise you may lock yourself out.
    </div>
</div>

<!-- ── 11. Google & Microsoft SSO ─────────────────────────────── -->
<div class="section" id="sso">
    <div class="section-num">SECTION 11</div>
    <h2>Google &amp; Microsoft SSO</h2>
    <p>Staff can log in with their Google Workspace or Microsoft 365 accounts using OAuth 2.0. You must register an application in the provider's developer console to get a client ID and secret.</p>

    <h3>Google Workspace Setup</h3>
    <ol>
        <li>Go to <strong>console.cloud.google.com</strong> &rarr; APIs &amp; Services &rarr; Credentials</li>
        <li>Create an OAuth 2.0 Client ID (type: Web application)</li>
        <li>Add an authorized redirect URI: <code>https://checkin.myschool.org/auth/callback/google</code></li>
        <li>Copy the Client ID and Client Secret</li>
        <li>Paste both into <strong>Admin &rarr; Setup &rarr; Authentication &rarr; Google</strong></li>
    </ol>

    <h3>Microsoft / Azure Setup</h3>
    <ol>
        <li>Go to <strong>portal.azure.com</strong> &rarr; Azure Active Directory &rarr; App registrations &rarr; New registration</li>
        <li>Set the redirect URI (Web): <code>https://checkin.myschool.org/auth/callback/microsoft</code></li>
        <li>Under Certificates &amp; secrets, create a new client secret; copy the value immediately</li>
        <li>Copy the Application (client) ID from the Overview page</li>
        <li>Copy the Directory (tenant) ID from the Overview page</li>
        <li>Paste all three values into <strong>Admin &rarr; Setup &rarr; Authentication &rarr; Microsoft</strong></li>
    </ol>

    <div class="callout callout-tip">
        <span class="callout-label">Domain restriction:</span>
        You can restrict SSO logins to your organization's domain (e.g. <code>@myschool.org</code>) in the Authentication settings. Users from other domains will be rejected even if they authenticate successfully with Google or Microsoft.
    </div>
</div>

<!-- ── 12. Post-Install Verification ─────────────────────────── -->
<div class="section" id="verify">
    <div class="section-num">SECTION 12</div>
    <h2>Post-Install Verification Checklist</h2>
    <p>After installation, run through these checks to confirm everything is working correctly.</p>

    <h3>Core functionality</h3>
    <ul class="check-list">
        <li>Navigate to <code>/checkin</code> — the visitor kiosk form loads</li>
        <li>Complete a test check-in — the visitor appears on the live board</li>
        <li>Navigate to <code>/board</code> — the live board shows the test visitor</li>
        <li>Navigate to <code>/depart</code> — search for the test visitor and check out</li>
        <li>Visitor disappears from the live board after check-out</li>
        <li>Admin dashboard at <code>/admin</code> loads without errors</li>
        <li>Live Logs at <code>/admin/live</code> shows the completed visit</li>
        <li>Visit History at <code>/admin/history</code> shows records with correct timestamps</li>
    </ul>

    <h3>Authentication</h3>
    <ul class="check-list">
        <li>Local account login works at <code>/auth/login</code></li>
        <li>If LDAP configured: log in with an AD username and password</li>
        <li>If Google SSO configured: click "Sign in with Google" and authenticate</li>
        <li>If Microsoft SSO configured: click "Sign in with Microsoft" and authenticate</li>
        <li>Logout works and redirects to the login page</li>
    </ul>

    <h3>Admin configuration</h3>
    <ul class="check-list">
        <li>At least one host (person being visited) is configured</li>
        <li>At least one visit reason is configured</li>
        <li>Organization name and time zone are correct in Settings</li>
        <li>Auto-checkout cron is running (check <code>/var/log/checkin-auto-checkout.log</code>)</li>
    </ul>
</div>

<!-- ── 13. Troubleshooting ───────────────────────────────────── -->
<div class="section" id="troubleshoot">
    <div class="section-num">SECTION 13</div>
    <h2>Troubleshooting</h2>

    <h3>404 on every page except /</h3>
    <p><code>mod_rewrite</code> is not enabled or <code>AllowOverride All</code> is missing from the virtual host. Run:</p>
<pre><code>a2enmod rewrite
systemctl reload apache2</code></pre>
    <p>Also verify the <code>&lt;Directory&gt;</code> block for <code>/var/www/checkin/public</code> has <code>AllowOverride All</code>.</p>

    <h3>Blank white page or 500 error</h3>
    <p>Check the Apache error log for the real error message:</p>
<pre><code>tail -50 /var/log/apache2/checkin_error.log</code></pre>
    <p>Common causes: missing PHP extension, PHP syntax error, database connection failure.</p>

    <h3>Install wizard appears after installation</h3>
    <p>The lock file is missing. If the install was completed, re-create it:</p>
<pre><code>touch /var/www/checkin/config/installed.lock
chown www-data:www-data /var/www/checkin/config/installed.lock</code></pre>

    <h3>Database connection fails during wizard</h3>
    <ul>
        <li>Verify the MySQL user has been granted access: <code>SHOW GRANTS FOR 'checkin_user'@'localhost';</code></li>
        <li>Test the connection directly: <code>mysql -u checkin_user -p checkin</code></li>
        <li>Check that MySQL is running: <code>systemctl status mysql</code></li>
    </ul>

    <h3>LDAP login says "Invalid credentials" even with correct password</h3>
    <ul>
        <li>Verify the service account bind DN is in full DN format, not UPN format</li>
        <li>Ensure the Directory Type is set to "Microsoft Active Directory" — this enables the correct subtree search</li>
        <li>Test with the Test Connection button; if that passes but login fails, check the Username Attribute field (<code>sAMAccountName</code> for AD)</li>
        <li>Check firewall: <code>telnet dc1.myschool.org 389</code></li>
    </ul>

    <h3>OAuth SSO returns "redirect_uri_mismatch"</h3>
    <p>The callback URL registered in Google or Microsoft does not exactly match the one the application sends. The URL must be:</p>
    <ul>
        <li>Google: <code>https://checkin.myschool.org/auth/callback/google</code></li>
        <li>Microsoft: <code>https://checkin.myschool.org/auth/callback/microsoft</code></li>
    </ul>
    <p>Check for trailing slashes, HTTP vs HTTPS, and port numbers. Copy the URL from the application's Authentication settings page to avoid typos.</p>

    <h3>Auto-checkout cron is not running</h3>
<pre><code># Check cron is active
systemctl status cron

# Check the crontab
crontab -l

# Run the script manually to test
php /var/www/checkin/scripts/auto_checkout.php</code></pre>

    <h3>Upgrading to a new version</h3>
    <p>Because the code is installed via git, updating is a single command:</p>
<pre><code>cd /var/www/checkin
git pull origin master</code></pre>
    <p>If there are database schema changes in the new release, the application will prompt you to run migrations. Any new migration files will be in <code>database/migrations/</code>.</p>
</div>

<!-- ── 14. Quick Reference Card ──────────────────────────────── -->
<div class="qrc" id="quickref">
    <h2>Quick Reference Card</h2>

    <table>
        <tr><th>Item</th><th>Value / Location</th></tr>
        <tr><td>Repository</td><td><code>https://github.com/ggreenaz/dadCHECKIN-TOO</code></td></tr>
        <tr><td>Web root</td><td><code>/var/www/checkin/public/</code></td></tr>
        <tr><td>Config files</td><td><code>/var/www/checkin/config/</code></td></tr>
        <tr><td>Local credentials file</td><td><code>config/database.local.php</code> (not in git)</td></tr>
        <tr><td>Lock file (disables wizard)</td><td><code>config/installed.lock</code></td></tr>
        <tr><td>Database schema</td><td><code>database/schema.sql</code></td></tr>
        <tr><td>Cron script</td><td><code>scripts/auto_checkout.php</code></td></tr>
        <tr><td>Install wizard URL</td><td><code>/install</code></td></tr>
        <tr><td>Admin login URL</td><td><code>/auth/login</code></td></tr>
        <tr><td>Admin dashboard</td><td><code>/admin</code></td></tr>
        <tr><td>Visitor kiosk</td><td><code>/checkin</code></td></tr>
        <tr><td>Check-out kiosk</td><td><code>/depart</code></td></tr>
        <tr><td>Live board</td><td><code>/board</code></td></tr>
        <tr><td>Apache error log</td><td><code>/var/log/apache2/checkin_error.log</code></td></tr>
        <tr><td>Auto-checkout log</td><td><code>/var/log/checkin-auto-checkout.log</code></td></tr>
        <tr><td>Reload Apache</td><td><code>systemctl reload apache2</code></td></tr>
        <tr><td>Update codebase</td><td><code>cd /var/www/checkin &amp;&amp; git pull</code></td></tr>
        <tr><td>Re-run install wizard</td><td><code>rm config/installed.lock</code></td></tr>
        <tr><td>Check PHP extensions</td><td><code>php -m | grep -E 'pdo|ldap|curl|mbstring'</code></td></tr>
    </table>
</div>

</body>
</html>
