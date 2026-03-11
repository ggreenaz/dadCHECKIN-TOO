<?php $docSub = 'Add and manage the people visitors come to see — staff members, departments, and offices.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Hosts are the staff members or offices that visitors select when checking in. When a visitor says "I'm here to see Mr. Johnson," Mr. Johnson needs to be on this list. Keeping hosts accurate and up to date ensures your visit records are meaningful and your Analytics data is useful.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Host list</div>
            <div class="doc-item-desc">All active hosts for your organization — name, department, and a delete button. Hosts are shown alphabetically.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Add Host form</div>
            <div class="doc-item-desc">A form to add a single host manually. Fields: Name (required), Department (optional).</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Click <strong>Add Host</strong>, enter the person's name, optionally select or type their department, and save.</li>
            <li>To remove a host, click the delete button next to their name. <strong>Note:</strong> deleting a host does not delete their past visits — historical records are preserved.</li>
            <li>For large numbers of hosts, use the <a href="/admin/docs/import">CSV Import</a> tool to upload a spreadsheet of names at once.</li>
            <li>If you use Active Directory/LDAP, hosts can be imported from your directory automatically using the seed script — contact your system administrator.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <div class="doc-item">
            <div class="doc-item-label">Departments</div>
            <div class="doc-item-desc">Assigning a department to a host makes the Busiest Hosts section of Analytics more informative and allows filtering by department in future reporting. Departments are managed separately in the guided setup at <a href="/admin/setup/departments">Setup → Departments</a>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Active vs. inactive</div>
            <div class="doc-item-desc">Only active hosts appear on the kiosk check-in form. Deleting a host sets them inactive — their visit history is retained.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Kiosk display order</div>
            <div class="doc-item-desc">Hosts are shown alphabetically on the kiosk. There is no manual sort order — keep host names consistent (e.g., always "First Last") for a clean list.</div>
        </div>
    </div>

</div>
