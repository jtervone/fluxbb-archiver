<?php
/**
 * User profile page content.
 *
 * Variables:
 * @var string $avatarHtml   Avatar HTML (img or placeholder)
 * @var array  $details      Array of ['label' => 'value'] for profile details
 * @var string|null $signatureHtml Signature HTML or null
 * @var string $signatureTitle Translated "Signature" title
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="user-profile">
  <div class="user-avatar">
    <?= $avatarHtml ?>
  </div>
  <div class="user-details">
    <dl>
      <?php foreach ($details as $label => $value): ?>
        <dt><?= $label ?></dt>
        <dd><?= $value ?></dd>
      <?php endforeach; ?>
    </dl>
    <?php if ($signatureHtml): ?>
      <div class="user-signature">
        <h4><?= $signatureTitle ?></h4>
        <?= $signatureHtml ?>
      </div>
    <?php endif; ?>
  </div>
</div>
