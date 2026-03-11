<?php
// Usage: include this at the top of every doc page.
// Requires: $docMeta['title'], $docMeta['back'], $docMeta['icon']
// Optional: $docSub (subtitle string)
?>
<div class="doc-page-nav">
    <a href="/admin/docs" class="doc-back-docs">&larr; All Docs</a>
    <span class="doc-nav-sep">/</span>
    <span><?= htmlspecialchars($docMeta['title']) ?></span>
    <form class="doc-nav-search" action="/admin/docs/search" method="GET">
        <input type="search" name="q" class="doc-nav-search-input"
               placeholder="Search docs…" autocomplete="off">
        <button type="submit" class="doc-nav-search-btn">&#128269;</button>
    </form>
    <a href="<?= htmlspecialchars($docMeta['back']) ?>" class="doc-open-page">
        Open <?= htmlspecialchars($docMeta['title']) ?> &rsaquo;
    </a>
</div>

<div class="doc-hero">
    <div class="doc-hero-icon"><?= $docMeta['icon'] ?></div>
    <div>
        <h1 class="doc-hero-title"><?= htmlspecialchars($docMeta['title']) ?></h1>
        <?php if (!empty($docSub)): ?>
            <p class="doc-hero-sub"><?= htmlspecialchars($docSub) ?></p>
        <?php endif; ?>
    </div>
</div>
