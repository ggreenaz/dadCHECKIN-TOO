<?php $docSub = 'Bulk upload hosts, visit reasons, or visitors from a CSV spreadsheet.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>CSV Import lets you add large numbers of records at once by uploading a spreadsheet instead of typing them in one at a time. You can import Hosts, Visit Reasons, and Visitors. This is most useful when first setting up the system or after a large staff change.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you can import</h2>
        <div class="doc-item">
            <div class="doc-item-label">Hosts</div>
            <div class="doc-item-desc">Upload a list of staff members. Required column: <code>name</code>. Optional column: <code>department</code>. Each row becomes one host.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visit Reasons</div>
            <div class="doc-item-desc">Upload a list of reason labels. Required column: <code>label</code>. Each row becomes one visit reason option on the kiosk.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visitors</div>
            <div class="doc-item-desc">Upload a pre-registered visitor list. Required column: <code>first_name</code>. Optional columns: <code>last_name</code>, <code>phone</code>, <code>email</code>. Useful for pre-populating expected visitors.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Download the template CSV for the type you want to import — the template shows the exact column headers required.</li>
            <li>Open the template in Excel, Google Sheets, or any spreadsheet app and fill in your data.</li>
            <li>Save the file as CSV (comma-separated values).</li>
            <li>On the Import page, choose the import type (Hosts, Reasons, or Visitors), select your file, and click Upload.</li>
            <li>Review the results — the system will report how many records were imported and flag any rows it could not process.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration &amp; important notes</h2>
        <div class="doc-item">
            <div class="doc-item-label">Duplicate handling</div>
            <div class="doc-item-desc">The importer skips records that would create exact duplicates (same name for hosts, same label for reasons). It will not overwrite existing records.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">File format</div>
            <div class="doc-item-desc">Files must be plain CSV with a header row. UTF-8 encoding is recommended. Maximum file size is determined by your server's PHP upload limit (typically 8MB).</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Active Directory / LDAP</div>
            <div class="doc-item-desc">If your organization uses Active Directory, hosts can be pulled directly from your directory using the LDAP integration rather than CSV import. See <a href="/admin/docs/settings">Settings → Authentication</a> for LDAP configuration.</div>
        </div>
    </div>

</div>
