<?php use App\Core\View; ?>

<div class="doc-index-header">
    <h1 class="doc-index-title">Help &amp; Documentation</h1>
    <p class="doc-index-sub">Select a page below to learn how it works and how to configure it.</p>

    <form class="doc-search-form" action="/admin/docs/search" method="GET">
        <div class="doc-search-wrap">
            <input type="search" name="q" class="doc-search-input"
                   placeholder="Search all documentation…"
                   value="<?= View::e($_GET['q'] ?? '') ?>"
                   autofocus autocomplete="off">
            <button type="submit" class="doc-search-btn">Search</button>
        </div>
    </form>
</div>

<div class="doc-index-grid">
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
