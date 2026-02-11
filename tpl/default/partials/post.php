<?php
/**
 * Single post/message article partial.
 *
 * Variables:
 * @var int         $postId       Post ID
 * @var int         $postNum      Post number in topic
 * @var string      $postDate     Formatted post date
 * @var string      $idPrefix     ID prefix ('p' for posts, 'm' for messages)
 * @var string      $body         Post body HTML (already parsed from BBCode)
 * @var string|null $signature    User signature HTML (already parsed) or null
 * @var string|null $editedNotice Edited notice HTML or null
 * @var string      $avatarHtml   Avatar HTML (img tag or placeholder div)
 * @var string      $usernameHtml Username HTML (link or plain text)
 * @var string      $userTitle    User title/group title (escaped)
 * @var array|null  $userDetails  Array of ['label' => 'value'] for user details, or null
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<article class="post" id="<?= $idPrefix . $postId ?>">
  <div class="post-header">
    <span class="post-date">#<?= $postNum ?> - <?= $postDate ?></span>
  </div>
  <div class="post-content">
    <div class="post-user-info">
      <div class="user-avatar"><?= $avatarHtml ?></div>
      <div class="username"><?= $usernameHtml ?></div>
      <?php if ($userTitle): ?>
        <div class="user-title"><?= $userTitle ?></div>
      <?php endif; ?>
      <?php if ($userDetails): ?>
        <dl class="user-details">
          <?php foreach ($userDetails as $label => $value): ?>
            <dt><?= $label ?></dt>
            <dd><?= $value ?></dd>
          <?php endforeach; ?>
        </dl>
      <?php endif; ?>
    </div>
    <div class="post-main">
      <div class="post-body"><?= $body ?></div>
      <?php if ($signature): ?>
        <div class="post-signature"><?= $signature ?></div>
      <?php endif; ?>
      <?php if ($editedNotice): ?>
        <div class="post-edited"><?= $editedNotice ?></div>
      <?php endif; ?>
    </div>
  </div>
</article>
