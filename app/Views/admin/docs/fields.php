<?php $docSub = 'Add extra questions to the check-in form beyond the standard name, phone, and email fields.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Custom Fields let you collect additional information from visitors at check-in that the standard form does not capture. For example: a badge number, a vehicle license plate, a company name, or an agreement checkbox. These fields appear on the kiosk after the standard fields.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">What you see on this page</h2>
        <div class="doc-item">
            <div class="doc-item-label">Fields list</div>
            <div class="doc-item-desc">All custom fields configured for your organization, showing the label, field type, and whether it is required.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Add Field form</div>
            <div class="doc-item-desc">Create a new field with a label, type, and required/optional setting.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Available field types</h2>
        <div class="doc-item">
            <div class="doc-item-label">Text</div>
            <div class="doc-item-desc">A single line of free text. Good for names, IDs, company names, or vehicle plates.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Checkbox</div>
            <div class="doc-item-desc">A yes/no tick box. Good for agreements, acknowledgements, or consent ("I agree to sign the visitor log").</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Select / Dropdown</div>
            <div class="doc-item-desc">A list of options the visitor picks from. Good for categories you define (e.g., "Contractor / Parent / Vendor / Other").</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Click <strong>Add Field</strong>, enter a label (what the visitor sees), choose a type, and decide if it is required.</li>
            <li>Required fields must be filled in before check-in can complete. Optional fields can be skipped.</li>
            <li>Keep custom fields to a minimum — every extra field slows down the check-in experience. Only add fields you will actually use.</li>
            <li>To remove a field, click delete. Data already collected for that field is retained in the database.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <div class="doc-item">
            <div class="doc-item-label">Kiosk visibility</div>
            <div class="doc-item-desc">Custom fields appear on the kiosk automatically once added here. Their order on the kiosk follows the order they were created. To reorder them, delete and re-add in the desired sequence.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Standard field visibility</div>
            <div class="doc-item-desc">The standard fields (Last Name, Phone, Email, Notes) can be shown or hidden separately in <a href="/admin/docs/kiosk">Kiosk Setup</a> — custom fields are in addition to those.</div>
        </div>
    </div>

</div>
