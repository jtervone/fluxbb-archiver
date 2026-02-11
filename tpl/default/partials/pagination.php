<?php
/**
 * Pagination navigation partial.
 *
 * Variables:
 * @var int    $currentPage Current page number
 * @var int    $totalPages  Total number of pages
 * @var string $baseUrl     Base URL for page links (e.g. 'forum_1')
 * @var string $firstText   Translated "First" text
 * @var string $prevText    Translated "Previous" text
 * @var string $nextText    Translated "Next" text
 * @var string $lastText    Translated "Last" text
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
if ($totalPages <= 1) return;

$start = max(1, $currentPage - 2);
$end = min($totalPages, $currentPage + 2);
?>
<nav class="pagination">
  <?php if ($currentPage > 1): ?>
    <a href="<?= $baseUrl ?>_p1.html">&laquo; <?= $firstText ?></a>
    <a href="<?= $baseUrl ?>_p<?= $currentPage - 1 ?>.html">&lsaquo; <?= $prevText ?></a>
  <?php endif; ?>
  <?php for ($i = $start; $i <= $end; $i++): ?>
    <?php if ($i === $currentPage): ?>
      <span class="current"><?= $i ?></span>
    <?php else: ?>
      <a href="<?= $baseUrl ?>_p<?= $i ?>.html"><?= $i ?></a>
    <?php endif; ?>
  <?php endfor; ?>
  <?php if ($currentPage < $totalPages): ?>
    <a href="<?= $baseUrl ?>_p<?= $currentPage + 1 ?>.html"><?= $nextText ?> &rsaquo;</a>
    <a href="<?= $baseUrl ?>_p<?= $totalPages ?>.html"><?= $lastText ?> &raquo;</a>
  <?php endif; ?>
</nav>
