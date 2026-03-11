<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>dadCHECKIN-TOO — End User Guide</title>
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
h1 { font-size: 2rem;   font-weight: 800; color: #1a1a2e; }
h2 { font-size: 1.25rem; font-weight: 700; color: #1a1a2e; margin: 0 0 10px; }
h3 { font-size: 1rem;   font-weight: 700; color: #1a1a2e; margin: 20px 0 6px; }
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
.toc h2 { font-size: 1rem; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 12px; }
.toc ol { margin: 0; padding-left: 20px; }
.toc li { margin-bottom: 5px; font-size: 0.95rem; }
.toc a  { text-decoration: none; color: #4f46e5; }
.toc a:hover { text-decoration: underline; }

/* ── SECTION ────────────────────────────────────────────────── */
.section {
    margin-bottom: 52px;
}
.section-header {
    display: flex;
    align-items: center;
    gap: 14px;
    background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
    color: #fff;
    border-radius: 10px 10px 0 0;
    padding: 16px 22px;
    margin-bottom: 0;
}
.section-num {
    background: rgba(255,255,255,.2);
    border-radius: 50%;
    width: 32px; height: 32px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; font-size: 0.9rem;
    flex-shrink: 0;
}
.section-icon { font-size: 1.4rem; }
.section-title { font-size: 1.1rem; font-weight: 700; }
.section-body {
    border: 1px solid #e2e8f0;
    border-top: none;
    border-radius: 0 0 10px 10px;
    padding: 24px 26px;
}

/* ── STEP BOXES ─────────────────────────────────────────────── */
.steps { list-style: none; padding: 0; margin: 14px 0; counter-reset: step; }
.steps li {
    counter-increment: step;
    display: flex;
    gap: 14px;
    align-items: flex-start;
    margin-bottom: 14px;
}
.steps li::before {
    content: counter(step);
    background: #4f46e5;
    color: #fff;
    border-radius: 50%;
    min-width: 26px; height: 26px;
    display: flex; align-items: center; justify-content: center;
    font-weight: 700; font-size: 0.8rem;
    flex-shrink: 0;
    margin-top: 1px;
}

/* ── FIELD TABLE ────────────────────────────────────────────── */
.field-table { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 0.9rem; }
.field-table th {
    background: #f1f5f9;
    text-align: left;
    padding: 8px 12px;
    font-weight: 700;
    border: 1px solid #e2e8f0;
}
.field-table td {
    padding: 9px 12px;
    border: 1px solid #e2e8f0;
    vertical-align: top;
}
.field-table tr:nth-child(even) td { background: #fafafa; }

/* ── CALLOUT BOXES ──────────────────────────────────────────── */
.callout {
    border-radius: 8px;
    padding: 14px 18px;
    margin: 16px 0;
    display: flex;
    gap: 12px;
    align-items: flex-start;
}
.callout-icon { font-size: 1.2rem; flex-shrink: 0; margin-top: 1px; }
.callout-tip  { background: #eff6ff; border-left: 4px solid #3b82f6; }
.callout-warn { background: #fffbeb; border-left: 4px solid #f59e0b; }
.callout-info { background: #f0fdf4; border-left: 4px solid #22c55e; }
.callout-body p:last-child { margin-bottom: 0; }

/* ── URL PILLS ──────────────────────────────────────────────── */
.url-pill {
    display: inline-block;
    background: #f1f5f9;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    padding: 4px 10px;
    font-family: monospace;
    font-size: 0.9rem;
    color: #4f46e5;
    margin: 2px 0;
}

/* ── ROLE BADGES ────────────────────────────────────────────── */
.role { display: inline-block; border-radius: 20px; padding: 2px 10px; font-size: 0.8rem; font-weight: 700; margin: 0 2px; }
.role-super  { background: #fef3c7; color: #92400e; }
.role-admin  { background: #ede9fe; color: #5b21b6; }
.role-loc    { background: #dbeafe; color: #1e40af; }
.role-staff  { background: #d1fae5; color: #065f46; }

/* ── SCREEN LABELS ──────────────────────────────────────────── */
.screen-label {
    display: inline-block;
    background: #1a1a2e;
    color: #fff;
    border-radius: 5px;
    padding: 2px 9px;
    font-size: 0.8rem;
    font-weight: 600;
    letter-spacing: .03em;
}

/* ── FAQ ────────────────────────────────────────────────────── */
.faq-item { margin-bottom: 22px; }
.faq-q { font-weight: 700; color: #1a1a2e; margin-bottom: 6px; display: flex; gap: 8px; }
.faq-q::before { content: "Q."; color: #4f46e5; font-weight: 800; flex-shrink: 0; }
.faq-a { padding-left: 26px; color: #334155; }

/* ── QUICK REF ──────────────────────────────────────────────── */
.qr-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 14px;
    margin-top: 16px;
}
.qr-card {
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
}
.qr-card-title { font-weight: 700; font-size: 0.9rem; margin-bottom: 6px; color: #4f46e5; }
.qr-card p { font-size: 0.85rem; margin-bottom: 0; color: #475569; }

/* ── FOOTER ─────────────────────────────────────────────────── */
.guide-footer {
    margin-top: 60px;
    padding-top: 20px;
    border-top: 2px solid #e2e8f0;
    text-align: center;
    color: #94a3b8;
    font-size: 0.8rem;
}

/* ── PRINT STYLES ───────────────────────────────────────────── */
@media print {
    @page { margin: 2cm 2.2cm; size: letter; }
    body  { font-size: 11px; padding: 0; max-width: 100%; color: #000; }
    .screen-only { display: none !important; }
    .section { page-break-inside: avoid; }
    .section-header { background: #1a1a2e !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .cover { page-break-after: always; }
    h1, h2, h3 { page-break-after: avoid; }
    .callout { border: 1px solid #ccc; page-break-inside: avoid; }
    a { color: #000; text-decoration: none; }
    .url-pill { background: #f5f5f5; border: 1px solid #ccc; color: #000; }
    .role { border: 1px solid #ccc; background: #f5f5f5 !important; color: #000 !important; }
}
</style>
</head>
<body>

<!-- Screen controls (hidden on print) -->
<div class="screen-only">
    <a href="/admin/docs" class="btn-back">&larr; Back to Docs</a>
    <button class="btn-print" onclick="window.print()">&#128438; Save / Print Guide</button>
</div>

<!-- ══════════════════════════════════════════════════════════
     COVER PAGE
     ══════════════════════════════════════════════════════════ -->
<div class="cover">
    <div class="cover-logo">✓</div>
    <h1>dadCHECKIN-TOO</h1>
    <p class="cover-sub">Visitor Management System — End User Guide</p>
    <p class="cover-ver">Version 2.0 &nbsp;|&nbsp; <?= date('F Y') ?></p>
</div>

<!-- ══════════════════════════════════════════════════════════
     TABLE OF CONTENTS
     ══════════════════════════════════════════════════════════ -->
<div class="toc">
    <h2>Table of Contents</h2>
    <ol>
        <li><a href="#intro">Introduction — What dadCHECKIN-TOO Does</a></li>
        <li><a href="#urls">Your System's Web Addresses</a></li>
        <li><a href="#checkin">Checking a Visitor In</a></li>
        <li><a href="#checkout">Checking a Visitor Out</a></li>
        <li><a href="#board">The Live Board (Display Screen)</a></li>
        <li><a href="#login">Logging In to the Admin Panel</a></li>
        <li><a href="#dashboard">The Admin Dashboard</a></li>
        <li><a href="#livelogs">Live Logs — Who's Inside Right Now</a></li>
        <li><a href="#loghub">The Log Hub — Central Command</a></li>
        <li><a href="#history">Visit History — Looking Up Past Visits</a></li>
        <li><a href="#visitors">Visitor Profiles</a></li>
        <li><a href="#hosts">Managing Hosts</a></li>
        <li><a href="#reasons">Managing Visit Reasons</a></li>
        <li><a href="#bulk">Bulk Check-Out (End of Day)</a></li>
        <li><a href="#roles">User Roles &amp; What Each Can Do</a></li>
        <li><a href="#faq">Common Situations &amp; FAQs</a></li>
        <li><a href="#quickref">Quick Reference Card</a></li>
    </ol>
</div>

<!-- ══════════════════════════════════════════════════════════
     1. INTRODUCTION
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="intro">
    <div class="section-header">
        <div class="section-num">1</div>
        <div class="section-icon">📋</div>
        <div class="section-title">Introduction — What dadCHECKIN-TOO Does</div>
    </div>
    <div class="section-body">
        <p>dadCHECKIN-TOO is a web-based visitor management system designed for schools and offices. It replaces paper sign-in logs with a digital kiosk that records every visitor's arrival, who they are visiting, why they are visiting, and when they leave.</p>

        <h3>The Three Things It Does</h3>
        <ul>
            <li><strong>Check visitors in</strong> — A touchscreen-friendly form captures visitor information when someone arrives at the front desk.</li>
            <li><strong>Check visitors out</strong> — When a visitor leaves, a simple search finds their record and closes the visit.</li>
            <li><strong>Give administrators real-time visibility</strong> — Staff and administrators can see who is currently in the building, review visit history, run reports, and receive email notifications when specific visitors arrive.</li>
        </ul>

        <h3>How It's Different from the Old System</h3>
        <p>The original dadtoo system was a simpler log. dadCHECKIN-TOO version 2 adds:</p>
        <ul>
            <li>A real-time Live Board for display screens in the main office</li>
            <li>Visitor profiles that track return visitors automatically</li>
            <li>Email notification rules (e.g., notify the principal when a specific reason is selected)</li>
            <li>A Log Hub that combines multiple views into a single command center</li>
            <li>Analytics — peak hours, busiest days, top hosts and reasons</li>
            <li>LDAP/Active Directory login — staff use their existing school network credentials</li>
            <li>Role-based access — different staff get different levels of access</li>
            <li>Automatic end-of-day check-out for visits that were never closed</li>
        </ul>

        <div class="callout callout-info">
            <div class="callout-icon">✓</div>
            <div class="callout-body">
                <p><strong>No software to install.</strong> dadCHECKIN-TOO runs entirely in a web browser. Any device with a browser and internet access — a desktop, tablet, or iPad — can be used as a check-in kiosk or admin workstation.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     2. URLs
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="urls">
    <div class="section-header">
        <div class="section-num">2</div>
        <div class="section-icon">🌐</div>
        <div class="section-title">Your System's Web Addresses</div>
    </div>
    <div class="section-body">
        <p>dadCHECKIN-TOO has several web addresses, each serving a different purpose. Bookmark the ones you use most often.</p>

        <table class="field-table">
            <thead><tr><th>Address</th><th>Who Uses It</th><th>Purpose</th></tr></thead>
            <tbody>
                <tr>
                    <td><span class="url-pill">/checkin</span></td>
                    <td>Visitors, front desk staff</td>
                    <td>The public check-in kiosk form. This is the page you leave open on the front desk tablet or kiosk computer. No login required.</td>
                </tr>
                <tr>
                    <td><span class="url-pill">/depart</span></td>
                    <td>Visitors, front desk staff</td>
                    <td>The check-out page. Visitors or staff search by name or phone number to close a visit. No login required.</td>
                </tr>
                <tr>
                    <td><span class="url-pill">/board</span></td>
                    <td>Anyone in the main office</td>
                    <td>The live display board — shows who is currently inside with elapsed time. Designed for a TV or monitor on the wall. Updates automatically. No login required.</td>
                </tr>
                <tr>
                    <td><span class="url-pill">/admin</span></td>
                    <td>Admin staff</td>
                    <td>The admin dashboard. Requires login. Where you manage hosts, view reports, and configure the system.</td>
                </tr>
                <tr>
                    <td><span class="url-pill">/logs</span></td>
                    <td>Admin staff</td>
                    <td>The Log Hub — a combined view of live visitors, recent activity, and reporting tools. Requires login.</td>
                </tr>
                <tr>
                    <td><span class="url-pill">/auth/login</span></td>
                    <td>Admin staff</td>
                    <td>The staff login page. Redirected here automatically when visiting /admin without being logged in.</td>
                </tr>
            </tbody>
        </table>

        <div class="callout callout-tip">
            <div class="callout-icon">💡</div>
            <div class="callout-body">
                <p><strong>Kiosk setup tip:</strong> On the front desk tablet or dedicated kiosk computer, set the browser's home page to <span class="url-pill">/checkin</span> and enable kiosk mode (full-screen, no address bar). For the office display monitor, set the home page to <span class="url-pill">/board</span>.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     3. CHECK IN
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="checkin">
    <div class="section-header">
        <div class="section-num">3</div>
        <div class="section-icon">✅</div>
        <div class="section-title">Checking a Visitor In</div>
    </div>
    <div class="section-body">
        <p>The check-in page (<span class="url-pill">/checkin</span>) is the page visitors interact with. It should be open and waiting on the front desk at all times. It does not require a password.</p>

        <h3>Step-by-Step: Checking Someone In</h3>
        <ol class="steps">
            <li>The visitor approaches the front desk. Hand them the tablet, or assist them at the kiosk screen.</li>
            <li>The visitor types their <strong>First Name</strong>. This field is always required.</li>
            <li>They enter their <strong>Last Name</strong> (if shown). Your administrator controls whether this is shown and required.</li>
            <li>They enter their <strong>Phone Number</strong> (if shown). The phone number is used for quick look-up on check-out — entering it now saves time later.</li>
            <li>They enter their <strong>Email Address</strong> (if shown). Used for notification emails and visitor records.</li>
            <li>They select <strong>Who they are visiting</strong> from the dropdown — this is the host (a teacher, office staff member, or department).</li>
            <li>They select the <strong>Reason for Visit</strong> from the dropdown (e.g., Parent Meeting, Delivery, Volunteer, etc.).</li>
            <li>They may add an optional <strong>Note</strong> if the Notes field is enabled (e.g., "Dropping off lunch for Room 14").</li>
            <li>They tap or click <strong>Check In</strong>. A confirmation appears immediately and the form resets for the next visitor.</li>
        </ol>

        <div class="callout callout-info">
            <div class="callout-icon">🔄</div>
            <div class="callout-body">
                <p><strong>Returning visitors:</strong> dadCHECKIN-TOO recognizes returning visitors automatically. If the same name and phone number have been used before, the visit is linked to their existing visitor profile. Their full visit history is available in the Visitor Profile view.</p>
            </div>
        </div>

        <h3>What the Form Fields Mean</h3>
        <table class="field-table">
            <thead><tr><th>Field</th><th>Always Required?</th><th>Notes</th></tr></thead>
            <tbody>
                <tr><td>First Name</td><td>Yes</td><td>Minimum required field. Cannot be turned off.</td></tr>
                <tr><td>Last Name</td><td>Configurable</td><td>Shown and optional by default. Can be made required by an administrator.</td></tr>
                <tr><td>Phone Number</td><td>Configurable</td><td>Recommended. Used for check-out lookup by phone number.</td></tr>
                <tr><td>Email</td><td>Configurable</td><td>Used for notification emails. Optional by default.</td></tr>
                <tr><td>Who are you visiting?</td><td>Yes</td><td>Drop-down list of hosts managed by administrators.</td></tr>
                <tr><td>Reason for Visit</td><td>Yes</td><td>Drop-down list managed by administrators.</td></tr>
                <tr><td>Notes</td><td>No</td><td>Optional free text. Administrator can show or hide this field.</td></tr>
            </tbody>
        </table>

        <div class="callout callout-warn">
            <div class="callout-icon">⚠️</div>
            <div class="callout-body">
                <p><strong>If the Host or Reason drop-down is empty:</strong> An administrator needs to add hosts and visit reasons. Go to <span class="url-pill">/admin/hosts</span> and <span class="url-pill">/admin/reasons</span> to add them. The check-in form cannot be submitted without both a host and a reason selected.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     4. CHECK OUT
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="checkout">
    <div class="section-header">
        <div class="section-num">4</div>
        <div class="section-icon">🚪</div>
        <div class="section-title">Checking a Visitor Out</div>
    </div>
    <div class="section-body">
        <p>When a visitor leaves, their visit must be closed so the system knows they have departed. Open the check-out page at <span class="url-pill">/depart</span>. No login is required.</p>

        <h3>Step-by-Step: Checking Someone Out</h3>
        <ol class="steps">
            <li>Open <span class="url-pill">/depart</span> in the browser. You can keep this open in a separate tab from the check-in page.</li>
            <li>Type the visitor's <strong>last name</strong> or <strong>phone number</strong> in the search box.</li>
            <li>The system shows a list of matching visitors who are currently checked in.</li>
            <li>Click <strong>Check Out</strong> next to the correct visitor.</li>
            <li>The visit is closed. The system records the check-out time automatically.</li>
        </ol>

        <div class="callout callout-tip">
            <div class="callout-icon">💡</div>
            <div class="callout-body">
                <p><strong>Tip:</strong> If the visitor entered their phone number when checking in, searching by phone number is the fastest way to find them — especially if multiple visitors have the same last name.</p>
            </div>
        </div>

        <h3>What if Someone Forgets to Check Out?</h3>
        <p>If a visitor leaves without checking out, their visit stays open in the system. There are three ways to handle this:</p>
        <ul>
            <li><strong>Staff manually close it</strong> — Find the open visit in Live Logs or the Log Hub and use the bulk checkout tool.</li>
            <li><strong>Auto-checkout runs overnight</strong> — If your administrator has configured the auto-checkout feature, any visit still open at end of day (e.g., 5:00 PM) is automatically closed.</li>
            <li><strong>Bulk close at end of day</strong> — An administrator can use the Bulk Check-Out tool on the Live Logs page to close all stale visits at once. See Section 14.</li>
        </ul>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     5. LIVE BOARD
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="board">
    <div class="section-header">
        <div class="section-num">5</div>
        <div class="section-icon">📺</div>
        <div class="section-title">The Live Board (Display Screen)</div>
    </div>
    <div class="section-body">
        <p>The Live Board at <span class="url-pill">/board</span> is a read-only display designed to be shown on a TV or monitor in the main office. It shows every visitor currently checked in, who they are visiting, and how long they have been in the building.</p>

        <h3>Features of the Live Board</h3>
        <ul>
            <li><strong>Real-time updates</strong> — The board polls for new check-ins and check-outs automatically. It does not need to be refreshed manually.</li>
            <li><strong>Elapsed time</strong> — Shows how long each visitor has been in the building (e.g., 12m, 1h 04m).</li>
            <li><strong>Host and reason</strong> — Shows who each visitor is seeing and why.</li>
            <li><strong>No login required</strong> — Anyone who can see the screen can see who is in the building. Do not display this screen in a public-facing area if visitor privacy is a concern.</li>
        </ul>

        <div class="callout callout-info">
            <div class="callout-icon">📺</div>
            <div class="callout-body">
                <p><strong>Office display setup:</strong> Connect a computer or streaming device to your office monitor. Open a browser in full-screen mode (F11 on Windows, Cmd+Ctrl+F on Mac) to <span class="url-pill">/board</span>. The page will stay updated without any interaction.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     6. LOGGING IN
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="login">
    <div class="section-header">
        <div class="section-num">6</div>
        <div class="section-icon">🔐</div>
        <div class="section-title">Logging In to the Admin Panel</div>
    </div>
    <div class="section-body">
        <p>The admin panel is for staff only and requires a login. Go to <span class="url-pill">/admin</span> — if you are not logged in, you will be redirected to the login page automatically.</p>

        <h3>Login Methods</h3>
        <p>Your administrator configures which login methods are available. You may see one or more of the following:</p>

        <table class="field-table">
            <thead><tr><th>Login Method</th><th>How to Use It</th></tr></thead>
            <tbody>
                <tr>
                    <td><strong>Username (Directory)</strong></td>
                    <td>Enter your <strong>network username</strong> — the same short username you use to log into your school computer (e.g., <code>jsmith</code>, not <code>jsmith@school.org</code>). Then enter your Windows/network password.</td>
                </tr>
                <tr>
                    <td><strong>Local Account</strong></td>
                    <td>Enter your dadCHECKIN-TOO email address and the password set by your administrator. Used for accounts not connected to the school directory.</td>
                </tr>
                <tr>
                    <td><strong>Sign in with Google</strong></td>
                    <td>Click the Google button and sign in with your school Google Workspace account.</td>
                </tr>
                <tr>
                    <td><strong>Sign in with Microsoft</strong></td>
                    <td>Click the Microsoft button and sign in with your school Microsoft 365 account.</td>
                </tr>
            </tbody>
        </table>

        <div class="callout callout-warn">
            <div class="callout-icon">⚠️</div>
            <div class="callout-body">
                <p><strong>If you cannot log in:</strong> Make sure you are using your <em>short</em> network username (e.g., <code>jsmith</code>) and not your full email address (<code>jsmith@school.org</code>) — unless your administrator specifically set up email-based login. Contact your system administrator if the problem persists.</p>
            </div>
        </div>

        <h3>Signing Out</h3>
        <p>Click your initials in the top-right corner of any admin page, then click <strong>Sign Out</strong>. Always sign out when leaving a shared computer.</p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     7. DASHBOARD
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="dashboard">
    <div class="section-header">
        <div class="section-num">7</div>
        <div class="section-icon">🏠</div>
        <div class="section-title">The Admin Dashboard</div>
    </div>
    <div class="section-body">
        <p>After logging in, you land on the Dashboard at <span class="url-pill">/admin</span>. This is your command center.</p>

        <h3>What You See on the Dashboard</h3>

        <table class="field-table">
            <thead><tr><th>Element</th><th>What It Shows</th></tr></thead>
            <tbody>
                <tr><td><strong>Inside Now</strong></td><td>The number of visitors currently checked in and in the building.</td></tr>
                <tr><td><strong>Today's Total</strong></td><td>Every check-in that has happened today, including those who have already left.</td></tr>
                <tr><td><strong>Completed</strong></td><td>Visits that were properly checked out today.</td></tr>
                <tr><td><strong>Extended (2h+)</strong></td><td>Visitors who have been inside for more than 2 hours. A high number may indicate forgotten check-outs.</td></tr>
                <tr><td><strong>No Shows</strong></td><td>Visitors who were expected but did not check in, or visits manually marked as no-show.</td></tr>
                <tr><td><strong>All-Time Visits</strong></td><td>Total visits ever recorded in the system, including historical records.</td></tr>
                <tr><td><strong>Currently Inside</strong></td><td>A live list of today's checked-in visitors with elapsed time bars. Click any name to see their profile.</td></tr>
                <tr><td><strong>Checked Out Today</strong></td><td>Recent check-outs with duration — how long each person was in the building.</td></tr>
                <tr><td><strong>Last 7 Days chart</strong></td><td>A bar chart of daily visit volume for the past week. Highlights today's bar. Shows today's top visit reason.</td></tr>
                <tr><td><strong>Find a Visitor</strong></td><td>Quick search box — type a name or phone number to jump directly to visit history results.</td></tr>
            </tbody>
        </table>

        <h3>Navigation Tiles</h3>
        <p>The module tiles on the right side of the dashboard are shortcuts to every section of the system. Click any tile to go directly to that area.</p>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     8. LIVE LOGS
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="livelogs">
    <div class="section-header">
        <div class="section-num">8</div>
        <div class="section-icon">🟢</div>
        <div class="section-title">Live Logs — Who's Inside Right Now</div>
    </div>
    <div class="section-body">
        <p>Live Logs at <span class="url-pill">/admin/live</span> shows every visitor who is currently checked in, with real-time elapsed time and color-coded status bars. The page auto-refreshes every 60 seconds.</p>

        <h3>Reading the Elapsed Time Colors</h3>
        <table class="field-table">
            <thead><tr><th>Color</th><th>Meaning</th></tr></thead>
            <tbody>
                <tr><td style="color:#16a34a;font-weight:700;">Green bar</td><td>Visitor has been inside less than 1 hour. Normal.</td></tr>
                <tr><td style="color:#ca8a04;font-weight:700;">Yellow bar</td><td>Visitor has been inside 1–2 hours. Worth a check.</td></tr>
                <tr><td style="color:#dc2626;font-weight:700;">Red bar</td><td>Visitor has been inside more than 2 hours. Likely forgot to check out.</td></tr>
            </tbody>
        </table>

        <h3>Bulk Check-Out Tool</h3>
        <p>The toolbar at the top of the Live Logs page lets you select and check out multiple visitors at once:</p>
        <ol class="steps">
            <li>Set the <strong>flag threshold</strong> using the dropdown (e.g., "Flag visitors open longer than 24 hours").</li>
            <li>Click <strong>Select All Flagged</strong> to automatically check every visitor over that threshold.</li>
            <li>Or click individual checkboxes to select specific visitors.</li>
            <li>The <strong>Check Out Selected</strong> button appears showing how many are selected.</li>
            <li>Click it and confirm. Selected visits are closed immediately.</li>
        </ol>

        <div class="callout callout-warn">
            <div class="callout-icon">⚠️</div>
            <div class="callout-body">
                <p><strong>Note:</strong> The page auto-refreshes every 60 seconds, which clears your checkbox selections. Complete your bulk checkout before the countdown timer reaches zero, or act before the page reloads.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     9. LOG HUB
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="loghub">
    <div class="section-header">
        <div class="section-num">9</div>
        <div class="section-icon">🗂️</div>
        <div class="section-title">The Log Hub — Central Command</div>
    </div>
    <div class="section-body">
        <p>The Log Hub at <span class="url-pill">/logs</span> is a combined command center that puts everything in one place. It is designed for front office staff who need a single page to manage all visitor activity throughout the day.</p>

        <h3>What the Log Hub Contains</h3>
        <ul>
            <li><strong>Today's active visits</strong> — Who is currently inside, with elapsed time.</li>
            <li><strong>Recent check-outs</strong> — Who left today and when.</li>
            <li><strong>Quick check-out</strong> — Find and check out visitors without leaving the page.</li>
            <li><strong>Search</strong> — Look up any visitor by name or phone number.</li>
            <li><strong>Today's summary stats</strong> — Visit counts at a glance.</li>
        </ul>

        <div class="callout callout-info">
            <div class="callout-icon">💡</div>
            <div class="callout-body">
                <p><strong>Recommended for front desk staff:</strong> Keep the Log Hub open as your primary admin tab throughout the day. It gives you everything you need without switching between pages.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     10. VISIT HISTORY
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="history">
    <div class="section-header">
        <div class="section-num">10</div>
        <div class="section-icon">📋</div>
        <div class="section-title">Visit History — Looking Up Past Visits</div>
    </div>
    <div class="section-body">
        <p>Visit History at <span class="url-pill">/admin/history</span> is a searchable, filterable record of every visit — past and present. Use it to look up when a specific person was in the building, generate date-range reports, or verify visit records.</p>

        <h3>Searching and Filtering</h3>
        <table class="field-table">
            <thead><tr><th>Filter</th><th>How to Use It</th></tr></thead>
            <tbody>
                <tr><td><strong>Search box</strong></td><td>Type a visitor's name or phone number. Results update as you type. Works for partial names.</td></tr>
                <tr><td><strong>Date range</strong></td><td>Set a Start Date and End Date to view visits within a specific period. Leave both blank to see all visits.</td></tr>
                <tr><td><strong>Host</strong></td><td>Filter to show only visits to a specific host (teacher, staff member, or department).</td></tr>
                <tr><td><strong>Reason</strong></td><td>Filter by visit reason (e.g., show only "Delivery" visits, or only "Parent Meeting" visits).</td></tr>
                <tr><td><strong>Status</strong></td><td>Filter by visit status: All, Checked In (still inside), Completed, Auto-Completed, No Show, or Cancelled.</td></tr>
            </tbody>
        </table>

        <h3>Visit Statuses Explained</h3>
        <table class="field-table">
            <thead><tr><th>Status</th><th>Meaning</th></tr></thead>
            <tbody>
                <tr><td><strong>Checked In</strong></td><td>Visitor is currently in the building. Visit is still open.</td></tr>
                <tr><td><strong>Completed</strong></td><td>Visitor was properly checked out by staff.</td></tr>
                <tr><td><strong>Auto-Completed</strong></td><td>Visit was automatically closed by the end-of-day auto-checkout or by a bulk checkout action. The visitor left without checking out.</td></tr>
                <tr><td><strong>No Show</strong></td><td>A visit was created but the visitor never arrived, or the visit was manually marked as a no-show.</td></tr>
                <tr><td><strong>Cancelled</strong></td><td>The visit was cancelled before it started.</td></tr>
            </tbody>
        </table>

        <div class="callout callout-tip">
            <div class="callout-icon">💡</div>
            <div class="callout-body">
                <p><strong>Running a report:</strong> Use the Date Range filter combined with the Host or Reason filter to answer specific questions — for example, "Show me all parent visits during the week of March 10" or "How many deliveries did we receive in February?"</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     11. VISITOR PROFILES
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="visitors">
    <div class="section-header">
        <div class="section-num">11</div>
        <div class="section-icon">👤</div>
        <div class="section-title">Visitor Profiles</div>
    </div>
    <div class="section-body">
        <p>When the same person checks in more than once, dadCHECKIN-TOO links their visits to a single visitor profile. Click any visitor's name in the Dashboard, Live Logs, or Visit History to open their profile.</p>

        <h3>What a Visitor Profile Shows</h3>
        <ul>
            <li><strong>Name, phone, and email</strong> — their contact information as entered during check-in.</li>
            <li><strong>Total visits</strong> — how many times they have visited in total.</li>
            <li><strong>First visit / Most recent visit</strong> — dates of their first and most recent check-in.</li>
            <li><strong>Full visit history</strong> — every individual visit, with check-in and check-out times, host visited, reason, and duration.</li>
        </ul>

        <div class="callout callout-info">
            <div class="callout-icon">ℹ️</div>
            <div class="callout-body">
                <p>Visitor matching uses the name and phone number entered at check-in. If a returning visitor spells their name differently or uses a different phone number, a new profile is created rather than being matched to the existing one.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     12. MANAGING HOSTS
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="hosts">
    <div class="section-header">
        <div class="section-num">12</div>
        <div class="section-icon">👥</div>
        <div class="section-title">Managing Hosts</div>
    </div>
    <div class="section-body">
        <p>Hosts are the people or departments that visitors come to see — teachers, office staff, departments, or specific locations. The host list populates the "Who are you visiting?" dropdown on the check-in form. Manage hosts at <span class="url-pill">/admin/hosts</span>.</p>

        <h3>Adding a Host</h3>
        <ol class="steps">
            <li>Go to <span class="url-pill">/admin/hosts</span>.</li>
            <li>Fill in the host's <strong>Name</strong> (required — e.g., "Mrs. Johnson" or "Main Office").</li>
            <li>Optionally assign a <strong>Department</strong> (e.g., "3rd Grade," "Administration," "Special Education"). This appears in the dropdown alongside the name to help visitors choose the right person.</li>
            <li>Click <strong>Add Host</strong>. They appear immediately in the check-in form dropdown.</li>
        </ol>

        <h3>Removing a Host</h3>
        <p>Click the <strong>Delete</strong> button next to a host's name. Deleting a host does not delete their historical visits — past records are preserved. The host will simply no longer appear as a choice for new check-ins.</p>

        <div class="callout callout-tip">
            <div class="callout-icon">💡</div>
            <div class="callout-body">
                <p><strong>Bulk import:</strong> If you have many hosts to add at once, use the CSV import feature at <span class="url-pill">/admin/import</span>. Download the template, fill it in with your staff list, and upload it. Existing hosts are skipped — duplicates are not created.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     13. MANAGING VISIT REASONS
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="reasons">
    <div class="section-header">
        <div class="section-num">13</div>
        <div class="section-icon">📝</div>
        <div class="section-title">Managing Visit Reasons</div>
    </div>
    <div class="section-body">
        <p>Visit reasons define why someone is visiting — they appear in the "Reason for Visit" dropdown on the check-in form and in all reports. Customize the list to match your organization's needs at <span class="url-pill">/admin/reasons</span>.</p>

        <h3>Common Reason Examples</h3>
        <ul>
            <li>Parent / Guardian Meeting</li>
            <li>Student Pick-Up</li>
            <li>Volunteer</li>
            <li>Delivery</li>
            <li>Maintenance / Contractor</li>
            <li>Interview / Applicant</li>
            <li>Administrative</li>
            <li>Emergency</li>
        </ul>

        <h3>Adding and Removing Reasons</h3>
        <p>Type the reason label in the field and click <strong>Add Reason</strong>. To remove one, click <strong>Delete</strong>. Like hosts, deleting a reason does not remove it from past visit records — only from future check-ins.</p>

        <div class="callout callout-warn">
            <div class="callout-icon">⚠️</div>
            <div class="callout-body">
                <p><strong>Keep the list short and specific.</strong> If you have too many reasons, visitors will spend time searching through the dropdown instead of checking in quickly. Aim for 5–10 clear, distinct reasons.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     14. BULK CHECKOUT
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="bulk">
    <div class="section-header">
        <div class="section-num">14</div>
        <div class="section-icon">🔄</div>
        <div class="section-title">Bulk Check-Out (End of Day)</div>
    </div>
    <div class="section-body">
        <p>At the end of the school day, there are often visitors who left without checking out — their visits remain "open" in the system. The Bulk Check-Out tool on the Live Logs page lets you close all of them at once.</p>

        <h3>End-of-Day Procedure</h3>
        <ol class="steps">
            <li>Go to <span class="url-pill">/admin/live</span>.</li>
            <li>Set the flag threshold to the appropriate hours (e.g., 8 hours for a school day).</li>
            <li>Click <strong>Select All Flagged</strong> — all visits over that threshold are checked.</li>
            <li>Review the selection. Uncheck anyone you know is still legitimately in the building.</li>
            <li>Click <strong>Check Out Selected</strong> and confirm when prompted.</li>
            <li>The system closes all selected visits and shows a confirmation count.</li>
        </ol>

        <div class="callout callout-info">
            <div class="callout-icon">ℹ️</div>
            <div class="callout-body">
                <p><strong>Auto-checkout alternative:</strong> Your administrator can set up automatic end-of-day checkout so you do not need to do this manually. When enabled, the system automatically closes all open visits at a configured time (e.g., 5:00 PM) every day. Ask your administrator if this is configured.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     15. USER ROLES
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="roles">
    <div class="section-header">
        <div class="section-num">15</div>
        <div class="section-icon">🔑</div>
        <div class="section-title">User Roles &amp; What Each Can Do</div>
    </div>
    <div class="section-body">
        <p>Every staff member who logs in to the admin panel has one of four roles. Your role determines what you can see and do.</p>

        <table class="field-table">
            <thead>
                <tr>
                    <th>Role</th>
                    <th>Typical User</th>
                    <th>What They Can Do</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><span class="role role-super">Super Admin</span></td>
                    <td>IT Administrator</td>
                    <td>Everything — authentication setup, all settings, all users, all data. Has the system-level break-glass account.</td>
                </tr>
                <tr>
                    <td><span class="role role-admin">Org Admin</span></td>
                    <td>Principal, Office Manager</td>
                    <td>All admin functions for their organization — settings, users, hosts, reasons, fields, reports, and notifications. Cannot change authentication providers.</td>
                </tr>
                <tr>
                    <td><span class="role role-loc">Location Admin</span></td>
                    <td>Building Secretary</td>
                    <td>Can view and manage visits, hosts, and reasons. Can run reports. Cannot change organization settings or manage users.</td>
                </tr>
                <tr>
                    <td><span class="role role-staff">Staff</span></td>
                    <td>Teacher, Office Assistant</td>
                    <td>Read-only access — can view Live Logs, Visit History, and the Log Hub. Cannot change any settings or data.</td>
                </tr>
            </tbody>
        </table>

        <h3>Managing Users</h3>
        <p>Only Org Admins and Super Admins can add or manage users. Go to <span class="url-pill">/admin/users</span> to:</p>
        <ul>
            <li>Add a new staff member and assign their role</li>
            <li>Promote or demote an existing user's role</li>
            <li>Deactivate a user who no longer works at the school (preserves their history)</li>
            <li>Reactivate a returning staff member</li>
        </ul>

        <div class="callout callout-warn">
            <div class="callout-icon">⚠️</div>
            <div class="callout-body">
                <p><strong>LDAP users:</strong> If your school uses Active Directory login, new staff may log in automatically on their first visit (in Open Mode). Their account is created with the <em>Staff</em> role. An Org Admin must promote them to a higher role if needed.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     16. FAQs
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="faq">
    <div class="section-header">
        <div class="section-num">16</div>
        <div class="section-icon">❓</div>
        <div class="section-title">Common Situations &amp; FAQs</div>
    </div>
    <div class="section-body">

        <div class="faq-item">
            <div class="faq-q">A visitor says they already checked out but they're still showing as inside.</div>
            <div class="faq-a">
                <p>The visitor may have closed the browser window or walked away without completing the check-out. Manually close their visit from Live Logs (<span class="url-pill">/admin/live</span>) by checking the box next to their name and clicking <strong>Check Out Selected</strong>.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">A visitor checked in with the wrong name or reason. Can I fix it?</div>
            <div class="faq-a">
                <p>Open Visit History, find the visit, and click the visitor's name to open their profile. Contact your administrator — editing past visit records requires Org Admin access. For simple corrections (wrong reason), a Location Admin or higher can edit the record.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">The check-in form shows no hosts in the dropdown. What's wrong?</div>
            <div class="faq-a">
                <p>No hosts have been added to the system yet, or all hosts have been deleted. An Org Admin or Location Admin needs to add hosts at <span class="url-pill">/admin/hosts</span> before visitors can check in.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">A staff member forgot their password.</div>
            <div class="faq-a">
                <p>For <strong>local accounts</strong>: An Org Admin can reset the password from <span class="url-pill">/admin/users</span> by editing the user's account. <br>
                For <strong>LDAP/network accounts</strong>: The password is managed by Active Directory. Contact your IT department to reset the network password — dadCHECKIN-TOO does not store or manage directory passwords.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">The Live Logs page shows hundreds of visitors still checked in.</div>
            <div class="faq-a">
                <p>This can happen after a data migration from the old dadtoo system, or if auto-checkout has not been running. Use the Bulk Check-Out tool with a threshold of 24 hours to close all stale records at once. After this, the Live Logs will show only genuinely active visitors.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">How do I see how many visitors came in last month?</div>
            <div class="faq-a">
                <p>Go to Visit History (<span class="url-pill">/admin/history</span>). Set the <strong>Start Date</strong> to the first day of last month and the <strong>End Date</strong> to the last day of last month. The total count is shown in the results header. You can also filter by host or reason to narrow it down further.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">Can I see which teacher had the most visitors?</div>
            <div class="faq-a">
                <p>Yes. Go to <span class="url-pill">/admin/analytics</span>. The <strong>Busiest Hosts</strong> chart shows the top hosts by visit count. You can also filter Visit History by host to see that teacher's complete visitor record.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">A visitor checks in every week. Do I have to enter their information every time?</div>
            <div class="faq-a">
                <p>Yes — visitors self-enter their information each time they check in. However, because dadCHECKIN-TOO matches visitors by name and phone number, all their visits are automatically linked to a single visitor profile. You can view their complete history in one place.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">The system seems slow. What should I do?</div>
            <div class="faq-a">
                <p>First, try refreshing the page. If the check-in page is slow, check that the tablet or kiosk device has a strong Wi-Fi connection. If the admin panel is slow, it may be because there are a very large number of active visits open — use the Bulk Check-Out tool to close stale visits, which will significantly improve performance.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">I need to add a visit manually for someone who didn't use the kiosk.</div>
            <div class="faq-a">
                <p>Open the check-in page at <span class="url-pill">/checkin</span> and complete the form on the visitor's behalf. Staff can use the check-in form at any time — it does not require visitor self-service. If you are logged into the admin panel, a "Back to Admin" link appears on the check-in page so you can return easily.</p>
            </div>
        </div>

        <div class="faq-item">
            <div class="faq-q">We had a fire drill. How do I check everyone out at once?</div>
            <div class="faq-a">
                <p>After the drill, go to Live Logs (<span class="url-pill">/admin/live</span>), click <strong>Select All Flagged</strong> (set threshold to 0 hours or the minimum), and click <strong>Check Out Selected</strong>. All visitors are checked out simultaneously with the current time recorded as their check-out time.</p>
            </div>
        </div>

    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     17. QUICK REFERENCE
     ══════════════════════════════════════════════════════════ -->
<div class="section" id="quickref">
    <div class="section-header">
        <div class="section-num">17</div>
        <div class="section-icon">⚡</div>
        <div class="section-title">Quick Reference Card</div>
    </div>
    <div class="section-body">
        <p>Tear out or print this section to keep at the front desk.</p>

        <div class="qr-grid">
            <div class="qr-card">
                <div class="qr-card-title">✅ Check In a Visitor</div>
                <p>Open <strong>/checkin</strong> → Fill out form → Tap Check In</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🚪 Check Out a Visitor</div>
                <p>Open <strong>/depart</strong> → Search name or phone → Click Check Out</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">👀 See Who's Inside</div>
                <p>Go to <strong>/admin/live</strong> or check the office display at <strong>/board</strong></p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🔍 Look Up a Past Visit</div>
                <p>Go to <strong>/admin/history</strong> → Search by name, date, host, or reason</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🔄 End-of-Day Checkout</div>
                <p>Go to <strong>/admin/live</strong> → Select All Flagged → Check Out Selected</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">👥 Add a New Host</div>
                <p>Go to <strong>/admin/hosts</strong> → Fill in name → Click Add Host</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">📋 Add a Visit Reason</div>
                <p>Go to <strong>/admin/reasons</strong> → Type label → Click Add Reason</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">📊 Run a Report</div>
                <p>Go to <strong>/admin/history</strong> → Set date range and filters</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">👤 Add a Staff User</div>
                <p>Go to <strong>/admin/users</strong> → Click Add User → Set role <em>(Org Admin only)</em></p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🏠 Admin Home</div>
                <p>Go to <strong>/admin</strong> — Dashboard with live count, quick search, and all navigation</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🗂️ Log Hub</div>
                <p>Go to <strong>/logs</strong> — Everything in one place for the front desk</p>
            </div>
            <div class="qr-card">
                <div class="qr-card-title">🆘 I Can't Log In</div>
                <p>Use your <strong>short network username</strong> (e.g., jsmith — not jsmith@school.org). Call IT if password reset is needed.</p>
            </div>
        </div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════════
     FOOTER
     ══════════════════════════════════════════════════════════ -->
<div class="guide-footer">
    <p><strong>dadCHECKIN-TOO</strong> — Visitor Management System &nbsp;|&nbsp; End User Guide &nbsp;|&nbsp; <?= date('Y') ?></p>
    <p>For system configuration help, see the Configuration Guide at <strong>/admin/docs/configuration</strong></p>
</div>

</body>
</html>
