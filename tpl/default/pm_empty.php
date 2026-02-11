<?php
/**
 * No private messages fallback page content.
 *
 * Variables:
 * @var string $noMessagesText  Translated no-messages text
 * @var string $privateDataTitle Translated title
 * @var array  $quickLinks       Array of ['url' => ..., 'text' => ...] quick links
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<p><?= $noMessagesText ?></p>

<h3><?= $privateDataTitle ?></h3>
<ul>
  <?php foreach ($quickLinks as $link): ?>
    <li><a href="<?= $link['url'] ?>"><?= $link['text'] ?></a></li>
  <?php endforeach; ?>
</ul>
