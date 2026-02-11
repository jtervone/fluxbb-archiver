<?php
/**
 * Forum topic listing page content.
 *
 * Variables:
 * @var string $forumDescription Forum description HTML or empty
 * @var string $paginationHtml   Pagination HTML
 * @var array  $topicRows        Topic row data arrays for partial rendering
 * @var string $thTopic          Column header: Topic
 * @var string $thAuthor         Column header: Author
 * @var string $thReplies        Column header: Replies
 * @var string $thViews          Column header: Views
 * @var string $thLastPost       Column header: Last Post
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<?php if ($forumDescription): ?>
  <p class="forum-desc"><?= $forumDescription ?></p>
<?php endif; ?>

<?= $paginationHtml ?>

<table class="topic-list">
  <thead>
    <tr>
      <th><?= $thTopic ?></th>
      <th><?= $thAuthor ?></th>
      <th><?= $thReplies ?></th>
      <th><?= $thViews ?></th>
      <th><?= $thLastPost ?></th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($topicRows as $row): ?>
      <?= $this->partial('topic_row', $row) ?>
    <?php endforeach; ?>
  </tbody>
</table>

<?= $paginationHtml ?>
