<?php
/**
 * User card in members grid.
 *
 * Variables:
 * @var int    $userId    User ID
 * @var string $username  Username (escaped)
 * @var string $userTitle User title (escaped)
 * @var string $postCount Formatted post count text
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="user-card">
  <a href="user_<?= $userId ?>.html"><?= $username ?></a>
  <div class="user-title"><?= $userTitle ?></div>
  <div><?= $postCount ?></div>
</div>
