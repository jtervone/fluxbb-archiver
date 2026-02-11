<?php
/**
 * Single topic row in forum topic table.
 *
 * Variables:
 * @var string $rowClass     CSS class(es) for the row
 * @var string $badges       Sticky/closed badges HTML
 * @var string $topicUrl     URL to the topic
 * @var string $subject      Topic subject (escaped)
 * @var string $authorLink   Author user link HTML
 * @var string $replies      Formatted reply count
 * @var string $views        Formatted view count
 * @var string $lastPostDate Formatted last post date
 * @var string $lastPosterLink Last poster user link HTML
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<tr class="<?= $rowClass ?>">
  <td><?= $badges ?><a href="<?= $topicUrl ?>"><?= $subject ?></a></td>
  <td><?= $authorLink ?></td>
  <td><?= $replies ?></td>
  <td><?= $views ?></td>
  <td><?= $lastPostDate ?><br><small>by <?= $lastPosterLink ?></small></td>
</tr>
