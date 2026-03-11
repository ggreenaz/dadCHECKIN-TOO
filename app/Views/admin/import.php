<?php use App\Core\View; ?>

<div class="card">
    <div class="card-title">CSV Import</div>
    <p style="color:var(--text-muted);font-size:0.875rem;margin-bottom:24px;">
        Upload a CSV file to bulk-import data. The first row can be a header — it will be detected and skipped automatically.
    </p>

    <!-- Hosts -->
    <div class="import-section">
        <div class="import-section-title">Import Hosts</div>
        <p class="import-desc">Expected columns: <code>name, email, phone, department</code> — only <strong>name</strong> is required.</p>
        <form method="POST" action="/admin/import/hosts" enctype="multipart/form-data" class="import-form">
            <div class="form-inline">
                <div class="form-group" style="flex:1;">
                    <input type="file" name="csv_file" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="button">Upload Hosts</button>
            </div>
        </form>
    </div>

    <hr class="import-divider">

    <!-- Reasons -->
    <div class="import-section">
        <div class="import-section-title">Import Visit Reasons</div>
        <p class="import-desc">Expected columns: <code>label</code></p>
        <form method="POST" action="/admin/import/reasons" enctype="multipart/form-data" class="import-form">
            <div class="form-inline">
                <div class="form-group" style="flex:1;">
                    <input type="file" name="csv_file" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="button">Upload Reasons</button>
            </div>
        </form>
    </div>

    <hr class="import-divider">

    <!-- Visitors -->
    <div class="import-section">
        <div class="import-section-title">Import Visitors (Pre-registration)</div>
        <p class="import-desc">Expected columns: <code>first_name, last_name, phone, email</code> — <strong>first_name</strong> and <strong>phone</strong> are required. Existing records matched by phone will be updated.</p>
        <form method="POST" action="/admin/import/visitors" enctype="multipart/form-data" class="import-form">
            <div class="form-inline">
                <div class="form-group" style="flex:1;">
                    <input type="file" name="csv_file" accept=".csv,text/csv" required>
                </div>
                <button type="submit" class="button">Upload Visitors</button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-title">CSV Format Examples</div>
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;font-size:0.825rem;">
        <div>
            <p style="font-weight:600;margin-bottom:6px;">Hosts</p>
            <pre style="background:var(--surface-2);padding:10px;border-radius:6px;overflow:auto;">name,email,phone,dept
Front Desk,,555-0100,Reception
HR Dept,hr@co.com,,Human Resources</pre>
        </div>
        <div>
            <p style="font-weight:600;margin-bottom:6px;">Visit Reasons</p>
            <pre style="background:var(--surface-2);padding:10px;border-radius:6px;overflow:auto;">label
Appointment
Delivery
Interview
General Inquiry</pre>
        </div>
        <div>
            <p style="font-weight:600;margin-bottom:6px;">Visitors</p>
            <pre style="background:var(--surface-2);padding:10px;border-radius:6px;overflow:auto;">first_name,last_name,phone,email
Jane,Doe,555-1234,jane@co.com
John,Smith,555-5678,</pre>
        </div>
    </div>
</div>
