<?php
/**
 * Single forum row in category listing.
 *
 * Variables:
 * @var string $icon         Forum icon HTML entity
 * @var string $forumUrl     URL to the forum page
 * @var string $forumName    Forum name (escaped)
 * @var string $description  Forum description HTML (parsed from BBCode) or empty
 * @var string $topicCount   Formatted topic count text
 * @var string $postCount    Formatted post count text
 * @var string $lastPoster   Last poster HTML (link or text) or empty
 * @var string $lastPosterLabel Translated "Last poster" label
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="forum-item">
  <div class="forum-icon"><?= $icon ?></div>
  <div class="forum-info">
    <h3><a href="<?= $forumUrl ?>"><?= $forumName ?></a></h3>
    <?php if ($description): ?>
      <p class="forum-desc"><?= $description ?></p>
    <?php endif; ?>
  </div>
  <div class="forum-stats">
    <?= $topicCount ?><br>
    <?= $postCount ?>
    <?php if ($lastPoster): ?>
      <br><small><?= $lastPosterLabel ?>: <?= $lastPoster ?></small>
    <?php endif; ?>
  </div>
</div>
