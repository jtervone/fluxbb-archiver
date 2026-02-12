<?php
/**
 * Base page layout — wraps all pages.
 *
 * Variables:
 * @var string $content       Page content HTML
 * @var string $title         Page title
 * @var string $boardTitle    Board/site name
 * @var string $lang          Language code
 * @var string $relativePath  Relative path to root
 * @var array  $breadcrumbs   Breadcrumb items: name => url (null for current)
 * @var array  $seo           SEO options (description, canonical, type, image, noindex)
 * @var string $generatedAt   Generation timestamp
 * @var \FluxbbArchiver\Html\TemplateEngine $this
 */

$description = $seo['description'] ?? '';
$canonical = $seo['canonical'] ?? '';
$ogType = $seo['type'] ?? 'website';
$ogImage = $seo['image'] ?? '';
$noindex = $seo['noindex'] ?? false;

if (strlen($description) > 160) {
    $description = substr($description, 0, 157) . '...';
}

// Build breadcrumbs HTML and structured data
$homeText = $translator->get('home');
$bcHtml = '<a href="' . $relativePath . 'index.html">' . $homeText . '</a>';
$bcItems = [['name' => $homeText, 'url' => $relativePath . 'index.html']];

foreach ($breadcrumbs as $name => $url) {
    $bcHtml .= ' &raquo; ';
    if ($url) {
        $bcHtml .= '<a href="' . $relativePath . $url . '">' . $this->h($name) . '</a>';
        $bcItems[] = ['name' => $name, 'url' => $relativePath . $url];
    } else {
        $bcHtml .= $this->h($name);
        $bcItems[] = ['name' => $name, 'url' => ''];
    }
}

// JSON-LD breadcrumbs
$breadcrumbLd = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [],
];
$position = 1;
foreach ($bcItems as $item) {
    $breadcrumbLd['itemListElement'][] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => $item['name'],
        'item' => $item['url'] ? $item['url'] : null,
    ];
}

$siteName = $boardTitle;
$fullTitle = $this->h($title) . ' - ' . $this->h($siteName);
$breadcrumbJson = json_encode($breadcrumbLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <?= $this->partial('head', [
      'title' => $title,
      'fullTitle' => $fullTitle,
      'lang' => $lang,
      'relativePath' => $relativePath,
      'description' => $description,
      'noindex' => $noindex,
      'canonical' => $canonical,
      'ogType' => $ogType,
      'ogImage' => $ogImage,
      'siteName' => $siteName,
      'breadcrumbJson' => $breadcrumbJson,
  ]) ?>
</head>
<body>
  <div class="container">
    <header>
      <h1><?= $this->h($title) ?></h1>
      <?= $this->partial('breadcrumbs', ['breadcrumbHtml' => $bcHtml]) ?>
    </header>
    <main>
      <?= $content ?>
    </main>
    <footer>
      <p><?= sprintf($translator->get('generated_on'), $generatedAt) ?></p>
      <p>Created with <a href="https://github.com/jtervone/fluxbb-archiver">FluxBB Archiver</a></p>
    </footer>
  </div>
</body>
</html>
