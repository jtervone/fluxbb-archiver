<?php
/**
 * Main forum index page content.
 *
 * Variables:
 * @var array  $categories     Public categories with forums
 * @var string $quickLinksTitle Translated quick links title
 * @var string $membersListText Translated members list text
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
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
  <h3><?= $quickLinksTitle ?></h3>
  <ul>
    <li><a href="users/index.html"><?= $membersListText ?></a></li>
  </ul>
</section>
