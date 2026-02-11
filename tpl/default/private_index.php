<?php
/**
 * Private forums index page content.
 *
 * Variables:
 * @var string $warningHtml       Warning banner HTML
 * @var array  $categories        Private categories with forumItems
 * @var string $otherDataTitle    Translated "Other private data" title
 * @var array  $quickLinks        Array of ['url' => ..., 'text' => ...] quick links
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<?= $warningHtml ?>

<?php foreach ($categories as $category): ?>
  <section class="category">
    <h2><?= $this->h($category['name']) ?></h2>
    <div class="forum-list">
      <?php foreach ($category['forumItems'] as $forumItem): ?>
        <?= $this->partial('forum_item', $forumItem) ?>
      <?php endforeach; ?>
    </div>
  </section>
<?php endforeach; ?>

<section class="quick-links">
  <h3><?= $otherDataTitle ?></h3>
  <ul>
    <?php foreach ($quickLinks as $link): ?>
      <li><a href="<?= $link['url'] ?>"><?= $link['text'] ?></a></li>
    <?php endforeach; ?>
  </ul>
</section>
