<?php
/**
 * Private messages index page content.
 *
 * Variables:
 * @var string $warningHtml       Warning banner HTML
 * @var array  $conversations     Conversation items with: url, subject, starterLink, recipientLink, preview
 * @var string $otherDataTitle    Translated "Other private data" title
 * @var array  $quickLinks        Array of ['url' => ..., 'text' => ...] quick links
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<?= $warningHtml ?>

<ul class="message-list">
  <?php foreach ($conversations as $pm): ?>
    <li class="message-item">
      <a href="<?= $pm['url'] ?>"><strong><?= $pm['subject'] ?></strong></a>
      <div class="message-participants">
        <?= $pm['starterLink'] ?> &harr; <?= $pm['recipientLink'] ?>
      </div>
      <div class="message-preview"><?= $pm['preview'] ?></div>
    </li>
  <?php endforeach; ?>
</ul>

<h3><?= $otherDataTitle ?></h3>
<ul>
  <?php foreach ($quickLinks as $link): ?>
    <li><a href="<?= $link['url'] ?>"><?= $link['text'] ?></a></li>
  <?php endforeach; ?>
</ul>
