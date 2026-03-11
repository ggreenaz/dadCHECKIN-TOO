<?php use App\Core\View; ?>

<div class="doc-index-header">
    <h1 class="doc-index-title">Search Documentation</h1>
    <p class="doc-index-sub">
        <?php if ($q !== ''): ?>
            <?= count($results) ?> result<?= count($results) !== 1 ? 's' : '' ?> for
            &ldquo;<strong><?= View::e($q) ?></strong>&rdquo;
        <?php else: ?>
            Enter a term below to search all help pages.
        <?php endif; ?>
    </p>

    <form class="doc-search-form" action="/admin/docs/search" method="GET">
        <div class="doc-search-wrap">
            <input type="search" name="q" class="doc-search-input"
                   placeholder="Search all documentation…"
                   value="<?= View::e($q) ?>"
                   autofocus autocomplete="off">
            <button type="submit" class="doc-search-btn">Search</button>
        </div>
    </form>
</div>

<?php if ($q !== '' && empty($results)): ?>
    <div class="doc-search-empty">
        <div class="doc-search-empty-icon">&#128269;</div>
        <div class="doc-search-empty-msg">No documentation found for &ldquo;<?= View::e($q) ?>&rdquo;</div>
        <p style="color:var(--text-muted);font-size:0.875rem;">Try a shorter or different term, or browse all topics below.</p>
    </div>

    <!-- Fall back to showing all doc pages -->
    <div class="doc-index-grid" style="margin-top:24px;">
    <?php foreach ($docPages as $slug => $meta): ?>
        <a href="/admin/docs/<?= $slug ?>" class="doc-index-card">
            <div class="doc-index-icon"><?= $meta['icon'] ?></div>
            <div class="doc-index-body">
                <div class="doc-index-name"><?= View::e($meta['title']) ?></div>
                <div class="doc-index-link">View documentation &rsaquo;</div>
            </div>
        </a>
    <?php endforeach; ?>
    </div>

<?php elseif (!empty($results)): ?>
    <div class="doc-search-results">
        <?php foreach ($results as $r): ?>
            <a href="/admin/docs/<?= View::e($r['slug']) ?>" class="doc-search-result">
                <div class="doc-search-result-head">
                    <span class="doc-search-result-icon"><?= $r['icon'] ?></span>
                    <span class="doc-search-result-title"><?= View::e($r['title']) ?></span>
                    <span class="doc-search-result-arrow">&rsaquo;</span>
                </div>
                <?php foreach ($r['snippets'] as $snippet): ?>
                    <p class="doc-search-snippet"><?= $snippet ?></p>
                <?php endforeach; ?>
            </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
