<?php use App\Core\View; ?>

<style>
/* ── Theme Editor Styles ─────────────────────────────────────── */
.preset-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
    gap: 12px;
    margin-bottom: 8px;
}
.preset-card {
    border: 2px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    cursor: pointer;
    transition: border-color .15s, transform .15s;
}
.preset-card:hover { transform: translateY(-2px); border-color: var(--primary); }
.preset-card.selected { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(0,115,177,.2); }
.preset-swatch {
    display: flex;
    height: 44px;
}
.preset-swatch-primary { flex: 1; }
.preset-swatch-header  { width: 36px; }
.preset-label {
    padding: 6px 8px;
    font-size: 0.78rem;
    font-weight: 600;
    text-align: center;
    background: var(--surface);
    color: var(--text);
    border-top: 1px solid var(--border);
}

.color-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-top: 8px;
}
.color-row {
    display: flex;
    align-items: center;
    gap: 10px;
}
.color-row input[type="color"] {
    width: 44px;
    height: 44px;
    border: 2px solid var(--border);
    border-radius: 8px;
    padding: 2px;
    cursor: pointer;
    background: var(--surface);
}
.color-row label {
    font-size: 0.88rem;
    font-weight: 600;
    color: var(--text);
    flex: 1;
}
.color-row small {
    font-size: 0.75rem;
    color: var(--text-muted);
    display: block;
}

.logo-preview {
    margin-top: 10px;
    display: flex;
    align-items: center;
    gap: 16px;
    flex-wrap: wrap;
}
.logo-preview img {
    max-height: 64px;
    max-width: 200px;
    border: 1px solid var(--border);
    border-radius: 8px;
    padding: 6px;
    background: #fff;
}
.logo-remove {
    font-size: 0.82rem;
    color: var(--danger);
    cursor: pointer;
    text-decoration: underline;
    background: none;
    border: none;
    padding: 0;
}

/* Live preview pane */
.theme-preview {
    border: 1.5px solid var(--border);
    border-radius: 10px;
    overflow: hidden;
    margin-top: 8px;
}
.tp-header {
    padding: 10px 16px;
    display: flex;
    align-items: center;
    gap: 10px;
    font-weight: 700;
    font-size: 0.9rem;
}
.tp-body {
    padding: 16px;
    background: var(--bg, #f1f5f9);
}
.tp-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 10px;
}
.tp-btn {
    display: inline-block;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 0.85rem;
    font-weight: 600;
    color: #fff;
    border: none;
    margin-top: 4px;
}
</style>

<div class="card">
    <div class="card-title">Theme &amp; Appearance</div>

    <form method="POST" action="/admin/settings/theme" enctype="multipart/form-data" id="theme-form">

        <!-- ── Presets ─────────────────────────────────────────── -->
        <h3 style="font-size:.9rem;font-weight:700;margin-bottom:12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
            Preset Themes
        </h3>

        <?php
        $presets = [
            'default'  => ['label' => 'Default',   'primary' => '#0073b1', 'header_bg' => '#0c2340', 'bg' => '#f1f5f9'],
            'ocean'    => ['label' => 'Ocean',      'primary' => '#0d9488', 'header_bg' => '#134e4a', 'bg' => '#f0fdfa'],
            'forest'   => ['label' => 'Forest',     'primary' => '#16a34a', 'header_bg' => '#14532d', 'bg' => '#f0fdf4'],
            'sunset'   => ['label' => 'Sunset',     'primary' => '#ea580c', 'header_bg' => '#431407', 'bg' => '#fff7ed'],
            'purple'   => ['label' => 'Purple',     'primary' => '#7c3aed', 'header_bg' => '#2e1065', 'bg' => '#faf5ff'],
            'slate'    => ['label' => 'Slate',      'primary' => '#475569', 'header_bg' => '#0f172a', 'bg' => '#f8fafc'],
            'crimson'  => ['label' => 'Crimson',    'primary' => '#dc2626', 'header_bg' => '#450a0a', 'bg' => '#fff1f2'],
            'custom'   => ['label' => 'Custom',     'primary' => '#0073b1', 'header_bg' => '#0c2340', 'bg' => '#f1f5f9'],
        ];
        $current = $theme ?? [];
        $activePreset = $current['preset'] ?? 'default';
        ?>

        <div class="preset-grid" id="preset-grid">
            <?php foreach ($presets as $key => $p): ?>
            <div class="preset-card <?= $activePreset === $key ? 'selected' : '' ?>"
                 data-preset="<?= $key ?>"
                 data-primary="<?= $p['primary'] ?>"
                 data-header="<?= $p['header_bg'] ?>"
                 data-bg="<?= $p['bg'] ?>"
                 onclick="applyPreset(this)">
                <div class="preset-swatch">
                    <div class="preset-swatch-primary" style="background:<?= $p['primary'] ?>"></div>
                    <div class="preset-swatch-header"  style="background:<?= $p['header_bg'] ?>"></div>
                </div>
                <div class="preset-label"><?= $p['label'] ?></div>
            </div>
            <?php endforeach; ?>
        </div>
        <input type="hidden" name="preset" id="inp-preset" value="<?= View::e($activePreset) ?>">

        <!-- ── Custom Colors ──────────────────────────────────── -->
        <h3 style="font-size:.9rem;font-weight:700;margin:24px 0 12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
            Custom Colors
        </h3>
        <div class="color-grid">
            <div class="color-row">
                <input type="color" name="primary" id="cp-primary"
                       value="<?= View::e($current['primary'] ?? '#0073b1') ?>"
                       oninput="updatePreview()">
                <label>Primary Color <small>Buttons, links, accents</small></label>
            </div>
            <div class="color-row">
                <input type="color" name="header_bg" id="cp-header"
                       value="<?= View::e($current['header_bg'] ?? '#0c2340') ?>"
                       oninput="updatePreview()">
                <label>Header Background <small>Top navigation bar</small></label>
            </div>
            <div class="color-row">
                <input type="color" name="bg" id="cp-bg"
                       value="<?= View::e($current['bg'] ?? '#f1f5f9') ?>"
                       oninput="updatePreview()">
                <label>Page Background <small>Main page color</small></label>
            </div>
            <div class="color-row">
                <input type="color" name="header_text" id="cp-header-text"
                       value="<?= View::e($current['header_text'] ?? '#ffffff') ?>"
                       oninput="updatePreview()">
                <label>Header Text <small>Nav links and brand name</small></label>
            </div>
        </div>

        <!-- ── Live Preview ───────────────────────────────────── -->
        <h3 style="font-size:.9rem;font-weight:700;margin:24px 0 12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
            Live Preview
        </h3>
        <div class="theme-preview">
            <div class="tp-header" id="prev-header" style="background:<?= View::e($current['header_bg'] ?? '#0c2340') ?>;color:<?= View::e($current['header_text'] ?? '#ffffff') ?>">
                <?php if (!empty($current['logo'])): ?>
                    <img src="/uploads/logos/<?= View::e($current['logo']) ?>" id="prev-logo" style="height:28px;border-radius:4px;">
                <?php else: ?>
                    <span id="prev-logo" style="display:none;height:28px;border-radius:4px;"></span>
                <?php endif; ?>
                <span><?= View::e($org['name'] ?? 'Your Organization') ?></span>
                <span style="margin-left:auto;font-size:0.8rem;opacity:.7;">Dashboard &nbsp; History &nbsp; Settings</span>
            </div>
            <div class="tp-body" id="prev-body" style="background:<?= View::e($current['bg'] ?? '#f1f5f9') ?>">
                <div class="tp-card">
                    <strong style="font-size:.9rem;">Sample Card</strong>
                    <p style="font-size:.82rem;color:#64748b;margin:4px 0 8px;">This is what your admin pages will look like.</p>
                    <button class="tp-btn" id="prev-btn" style="background:<?= View::e($current['primary'] ?? '#0073b1') ?>">
                        Save Settings
                    </button>
                </div>
            </div>
        </div>

        <!-- ── Logo Upload ────────────────────────────────────── -->
        <h3 style="font-size:.9rem;font-weight:700;margin:24px 0 12px;color:var(--text-muted);text-transform:uppercase;letter-spacing:.05em;">
            Organization Logo
        </h3>
        <div class="form-group">
            <label for="logo">Upload Logo <span style="font-weight:400;color:var(--text-muted)">(PNG, JPG, SVG — max 2MB)</span></label>
            <input type="file" name="logo" id="logo" accept="image/png,image/jpeg,image/svg+xml,image/gif"
                   style="padding:8px 0;" onchange="previewLogo(this)">
        </div>

        <?php if (!empty($current['logo'])): ?>
        <div class="logo-preview" id="logo-preview-wrap">
            <img src="/uploads/logos/<?= View::e($current['logo']) ?>" alt="Current logo" id="logo-img-preview">
            <div>
                <div style="font-size:.85rem;font-weight:600;margin-bottom:4px;">Current logo</div>
                <button type="button" class="logo-remove" onclick="removeLogo()">Remove logo</button>
                <input type="hidden" name="remove_logo" id="remove-logo-inp" value="0">
            </div>
        </div>
        <?php else: ?>
        <div class="logo-preview" id="logo-preview-wrap" style="display:none;">
            <img src="" alt="Logo preview" id="logo-img-preview">
            <input type="hidden" name="remove_logo" id="remove-logo-inp" value="0">
        </div>
        <?php endif; ?>

        <!-- ── Actions ────────────────────────────────────────── -->
        <div class="form-actions" style="margin-top:28px;">
            <button type="submit" class="button">Save Theme</button>
            <button type="button" class="button" style="background:var(--surface-2);color:var(--text);border:1px solid var(--border);"
                    onclick="resetToDefault()">Reset to Default</button>
            <a href="/admin/settings" style="margin-left:auto;font-size:.875rem;color:var(--text-muted);align-self:center;">
                ← Back to Settings
            </a>
        </div>

    </form>
</div>

<script>
function applyPreset(el) {
    // Deselect all
    document.querySelectorAll('.preset-card').forEach(function(c) { c.classList.remove('selected'); });
    el.classList.add('selected');

    var preset = el.dataset.preset;
    document.getElementById('inp-preset').value = preset;

    if (preset !== 'custom') {
        document.getElementById('cp-primary').value     = el.dataset.primary;
        document.getElementById('cp-header').value      = el.dataset.header;
        document.getElementById('cp-bg').value          = el.dataset.bg;
        document.getElementById('cp-header-text').value = '#ffffff';
    }
    updatePreview();
}

function updatePreview() {
    var primary    = document.getElementById('cp-primary').value;
    var headerBg   = document.getElementById('cp-header').value;
    var bg         = document.getElementById('cp-bg').value;
    var headerText = document.getElementById('cp-header-text').value;

    document.getElementById('prev-header').style.background = headerBg;
    document.getElementById('prev-header').style.color      = headerText;
    document.getElementById('prev-body').style.background   = bg;
    document.getElementById('prev-btn').style.background    = primary;

    // Mark as custom if colors changed from selected preset
    document.getElementById('inp-preset').value = 'custom';
    document.querySelectorAll('.preset-card').forEach(function(c) { c.classList.remove('selected'); });
    document.querySelector('[data-preset="custom"]').classList.add('selected');
}

function previewLogo(input) {
    if (!input.files || !input.files[0]) return;
    var reader = new FileReader();
    reader.onload = function(e) {
        var img  = document.getElementById('logo-img-preview');
        var wrap = document.getElementById('logo-preview-wrap');
        var ph   = document.getElementById('prev-logo');
        img.src  = e.target.result;
        wrap.style.display = 'flex';
        if (ph) { ph.src = e.target.result; ph.style.display = 'block'; }
    };
    reader.readAsDataURL(input.files[0]);
}

function removeLogo() {
    document.getElementById('remove-logo-inp').value = '1';
    document.getElementById('logo-preview-wrap').style.display = 'none';
    var ph = document.getElementById('prev-logo');
    if (ph) ph.style.display = 'none';
}

function resetToDefault() {
    document.querySelector('[data-preset="default"]').click();
    document.getElementById('remove-logo-inp').value = '1';
    document.getElementById('logo-preview-wrap').style.display = 'none';
}
</script>
