<?php
/**
 * HTML <head> partial.
 *
 * Variables:
 * @var string $title       Page title
 * @var string $fullTitle   Full title with board name
 * @var string $lang        Language code (e.g. 'fi')
 * @var string $relativePath Relative path to root (e.g. '../')
 * @var string $description Meta description
 * @var bool   $noindex     Whether to add noindex
 * @var string $canonical   Canonical URL
 * @var string $ogType      Open Graph type
 * @var string $ogImage     Open Graph image
 * @var string $siteName    Board/site name
 * @var string $breadcrumbJson JSON-LD breadcrumb data
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<?php if ($description): ?>
  <meta name="description" content="<?= $this->h($description) ?>">
<?php endif; ?>
<?php if ($noindex): ?>
  <meta name="robots" content="noindex, nofollow">
<?php else: ?>
  <meta name="robots" content="index, follow">
<?php endif; ?>
<?php if ($canonical): ?>
  <link rel="canonical" href="<?= $this->h($canonical) ?>">
<?php endif; ?>

<!-- Open Graph / Facebook -->
<meta property="og:type" content="<?= $this->h($ogType) ?>">
<meta property="og:title" content="<?= $this->h($title) ?>">
<meta property="og:site_name" content="<?= $this->h($siteName) ?>">
<meta property="og:locale" content="fi_FI">
<?php if ($description): ?>
  <meta property="og:description" content="<?= $this->h($description) ?>">
<?php endif; ?>
<?php if ($ogImage): ?>
  <meta property="og:image" content="<?= $this->h($ogImage) ?>">
<?php endif; ?>

<!-- Twitter Card -->
<meta name="twitter:card" content="summary">
<meta name="twitter:title" content="<?= $this->h($title) ?>">
<?php if ($description): ?>
  <meta name="twitter:description" content="<?= $this->h($description) ?>">
<?php endif; ?>
<?php if ($ogImage): ?>
  <meta name="twitter:image" content="<?= $this->h($ogImage) ?>">
<?php endif; ?>

<title><?= $fullTitle ?></title>
<link rel="stylesheet" href="<?= $relativePath ?>css/style.css">

<!-- Structured Data -->
<script type="application/ld+json"><?= $breadcrumbJson ?></script>
