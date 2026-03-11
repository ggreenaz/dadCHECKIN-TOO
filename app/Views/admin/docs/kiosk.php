<?php $docSub = 'Configure what visitors see and do on the self-service check-in screen.'; ?>
<?php include __DIR__ . '/_header.php'; ?>

<div class="doc-body">

    <div class="doc-section">
        <h2 class="doc-section-title">What this page does</h2>
        <p>Kiosk Setup controls the visitor-facing check-in experience. The kiosk is the public page at <code>/checkin</code> — the screen you put on a tablet or computer at your front desk for visitors to use themselves. This page lets you choose which fields they fill in and how the form behaves.</p>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Standard fields you can show or hide</h2>
        <div class="doc-item">
            <div class="doc-item-label">First Name</div>
            <div class="doc-item-desc">Always required. Cannot be hidden — every visit record needs a name.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Last Name</div>
            <div class="doc-item-desc">On by default. Can be hidden if first name only is sufficient for your use case.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Phone</div>
            <div class="doc-item-desc">Useful for contact and for self-checkout (visitors can check out by entering their phone number). Recommended to keep on.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Email</div>
            <div class="doc-item-desc">Optional. Useful if you plan to send confirmation emails or use email for visitor lookup.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Notes</div>
            <div class="doc-item-desc">A free-text box visitors can use to leave a message. Optional and off by default — most organizations do not need this.</div>
        </div>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">How to use it</h2>
        <ol class="doc-ol">
            <li>Check the boxes next to the fields you want to appear on the kiosk, uncheck the ones you want to hide.</li>
            <li>Save your settings.</li>
            <li>Open <code>/checkin</code> in a browser to preview how the form looks to visitors.</li>
            <li>For a minimal, fast check-in experience, show only First Name, Last Name, and Phone.</li>
            <li>For more detailed records, add Email and enable custom fields in <a href="/admin/docs/fields">Custom Fields</a>.</li>
        </ol>
    </div>

    <div class="doc-section">
        <h2 class="doc-section-title">Configuration</h2>
        <div class="doc-item">
            <div class="doc-item-label">Self-checkout by phone</div>
            <div class="doc-item-desc">If the Phone field is enabled, visitors can check themselves out at the kiosk by entering their phone number. If Phone is hidden, only admins can check visitors out from the admin panel.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Host selection</div>
            <div class="doc-item-desc">The kiosk always asks "Who are you visiting?" and shows your host list. To change who appears, manage your hosts in <a href="/admin/docs/hosts">Manage Hosts</a>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Visit reason selection</div>
            <div class="doc-item-desc">The kiosk always asks "What is the purpose of your visit?" and shows your reasons list. To change the options, manage them in <a href="/admin/docs/reasons">Visit Reasons</a>.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Kiosk authentication</div>
            <div class="doc-item-desc">The kiosk can be locked with a PIN so only authorized visitors can use it, or left open for all. Configure the PIN in Kiosk Setup. Leave blank for an open kiosk.</div>
        </div>
        <div class="doc-item">
            <div class="doc-item-label">Custom fields</div>
            <div class="doc-item-desc">Any additional fields you create in <a href="/admin/docs/fields">Custom Fields</a> automatically appear on the kiosk below the standard fields.</div>
        </div>
    </div>

</div>
