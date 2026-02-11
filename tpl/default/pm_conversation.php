<?php
/**
 * Private message conversation page content.
 *
 * Variables:
 * @var string $participantsLabel Translated "Participants" label
 * @var string $starterLink      Starter user link HTML
 * @var string $recipientLink    Recipient user link HTML
 * @var array  $messages         Message data arrays for post partial rendering
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="message-participants">
  <strong><?= $participantsLabel ?>:</strong>
  <?= $starterLink ?> &harr; <?= $recipientLink ?>
</div>

<?php foreach ($messages as $msg): ?>
  <?= $this->partial('post', $msg) ?>
<?php endforeach; ?>
