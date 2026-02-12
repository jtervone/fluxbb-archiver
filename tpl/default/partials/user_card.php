<?php
/**
 * User card in members grid.
 *
 * Variables:
 * @var string $userSlug  User slug for URL
 * @var string $username  Username (escaped)
 * @var string $userTitle User title (escaped)
 * @var string $postCount Formatted post count text
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="user-card">
  <a href="<?= $userSlug ?>.html"><?= $username ?></a>
  <div class="user-title"><?= $userTitle ?></div>
  <div><?= $postCount ?></div>
</div>
