<?php
/**
 * Private users list (with email, etc.) page content.
 *
 * Variables:
 * @var string $warningHtml    Warning banner HTML
 * @var array  $users          User rows with: id, username, email, registered, numPosts (all escaped)
 * @var string $thUsername     Column header
 * @var string $thEmail        Column header
 * @var string $thRegistered   Column header
 * @var string $thPosts        Column header
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<?= $warningHtml ?>

<table class="topic-list">
  <thead>
    <tr>
      <th><?= $thUsername ?></th>
      <th><?= $thEmail ?></th>
      <th><?= $thRegistered ?></th>
      <th><?= $thPosts ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($users as $user): ?>
      <tr>
        <td><a href="user_<?= $user['id'] ?>.html"><?= $user['username'] ?></a></td>
        <td><?= $user['email'] ?></td>
        <td><?= $user['registered'] ?></td>
        <td><?= $user['numPosts'] ?></td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
