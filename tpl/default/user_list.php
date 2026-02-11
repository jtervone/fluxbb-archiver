<?php
/**
 * Members list page content.
 *
 * Variables:
 * @var array $userCards Array of user card data for partial rendering
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<div class="user-list">
  <?php foreach ($userCards as $card): ?>
    <?= $this->partial('user_card', $card) ?>
  <?php endforeach; ?>
</div>
