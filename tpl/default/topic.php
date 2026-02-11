<?php
/**
 * Topic page with posts.
 *
 * Variables:
 * @var string $paginationHtml Pagination HTML
 * @var array  $posts          Post data arrays for partial rendering
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<?= $paginationHtml ?>

<?php foreach ($posts as $post): ?>
  <?= $this->partial('post', $post) ?>
<?php endforeach; ?>

<?= $paginationHtml ?>
