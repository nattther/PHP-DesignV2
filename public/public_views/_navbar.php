<?php
declare(strict_types=1);

/** @var Design\Auth\AuthContext $auth */
/** @var string $viewName */
/** @var string $appName */
/** @var string $faviconRelative */
/** @var Design\Path\AssetPaths $assets */
/** @var string $baseUrl */

$cssFile = $assets->cssFilePath('output.css');
$cssUrl  = $assets->cssUrl('output.css');
$cssVer  = is_file($cssFile) ? (string) filemtime($cssFile) : '1';

$pageTitle = $viewName !== '' ? ucwords(str_replace(['-', '_'], ' ', $viewName)) : 'Accueil';
$fullTitle = $appName . ' | ' . $pageTitle;

$faviconUrl = $assets->assetUrl($faviconRelative);

// Logo
$logoUrl = $assets->assetUrl('img/logo/Lyreco_Logo.webp');

// Current path for active nav
$currentPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$currentPath = '/' . ltrim($currentPath, '/');

$navItems = [
    ['href' => '/',        'label' => 'Accueil'],
    ['href' => '/contact', 'label' => 'Contact'],
];



$normalize = static fn(string $href): string => '/' . ltrim((string) parse_url($href, PHP_URL_PATH), '/');

$isActive = static function (string $href) use ($currentPath, $normalize): bool {
    $target = $normalize($href);
    return $target === '/' ? ($currentPath === '/' || $currentPath === '') : str_starts_with($currentPath, $target);
};

$renderNavLink = static function (array $item, bool $mobile = false) use ($baseUrl, $isActive): string {
    $url = rtrim($baseUrl, '/') . $item['href'];
    $active = $isActive($item['href']);

    if ($mobile) {
        $cls = $active
            ? 'w-full rounded-2xl px-4 py-3 font-semibold bg-lyreco-blue text-lyreco-white shadow-sm'
            : 'w-full rounded-2xl px-4 py-3 font-semibold text-lyreco-dark bg-lyreco-dark-white hover:bg-lyreco-green/10 border border-lyreco-light-gray/70';
        return '<a href="' . htmlspecialchars($url) . '" class="' . $cls . '"' . ($active ? ' aria-current="page"' : '') . '>'
            . htmlspecialchars($item['label']) . '</a>';
    }

    $base = 'inline-flex items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold tracking-wide transition
             focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-ring focus-visible:ring-offset-2 focus-visible:ring-offset-surface';

    if ($active) {
        $cls = $base . ' bg-lyreco-blue text-lyreco-white shadow-sm';
        $aria = ' aria-current="page"';
    } else {
        $cls = $base . ' text-lyreco-dark-gray bg-lyreco-white/80 hover:bg-lyreco-green/10 hover:text-lyreco-blue border border-lyreco-light-gray/70';
        $aria = '';
    }

    return '<a href="' . htmlspecialchars($url) . '" class="' . $cls . '"' . $aria . '>'
        . htmlspecialchars($item['label']) . '</a>';
};
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title><?= htmlspecialchars($fullTitle) ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($cssUrl . '?v=' . $cssVer) ?>">
  <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>" sizes="any">
</head>

<body class="font-noto-sans bg-lyreco-dark-white text-lyreco-dark min-h-screen flex flex-col">
<a class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-xl focus:bg-lyreco-white focus:px-4 focus:py-2 focus:shadow"
   href="#content">
  Aller au contenu
</a>

<?php if ($auth->isForbidden()): ?>
  <header class="sticky top-0 z-50 bg-lyreco-dark-white/90 backdrop-blur border-b border-lyreco-light-gray/70">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between">
      <a href="<?= htmlspecialchars(rtrim($baseUrl, '/') . '/') ?>" class="inline-flex items-center gap-3">
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="<?= htmlspecialchars($appName) ?>"
             class="h-10 w-10 rounded-full bg-white object-contain ring-2 ring-lyreco-blue/15">
        <span class="font-extrabold tracking-tight text-lyreco-blue"><?= htmlspecialchars($appName) ?></span>
      </a>
      <span class="text-sm text-lyreco-dark-gray">Accès restreint</span>
    </div>
  </header>
  <?php return; ?>
<?php endif; ?>

<header class="sticky top-0 z-50 bg-lyreco-dark-white/90 backdrop-blur border-b border-lyreco-green/70">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="py-4 flex items-center justify-between gap-4">

      <!-- Brand -->
      <a href="<?= htmlspecialchars(rtrim($baseUrl, '/') . '/') ?>"
         class="group inline-flex items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-lyreco-white/80">
        <img
          src="<?= htmlspecialchars($logoUrl) ?>"
          alt="<?= htmlspecialchars($appName) ?>"
          class="h-14 w-14 rounded-full bg-white object-contain ring-2 ring-lyreco-blue/15"
          loading="lazy"
        >
        <div class="leading-tight">
          <div class="text-base font-extrabold tracking-tight text-lyreco-blue group-hover:text-lyreco-blue-hover">
            <?= htmlspecialchars($appName) ?>
                       <span class="text-[11px] font-semibold text-lyreco-dark-gray translate-y-[1px]">
                by Hive
              </span>
          </div>

          <div class="text-sm text-lyreco-dark-gray">
            <?= htmlspecialchars($pageTitle) ?>
          </div>

        </div>
        
      </a>

      <!-- Desktop nav -->
      <nav class="hidden md:flex items-center gap-3" aria-label="Navigation principale">
        <?php foreach ($navItems as $item): ?>
          <?= $renderNavLink($item, false) ?>
        <?php endforeach; ?>
      </nav>

      <!-- Mobile button -->
      <button
        type="button"
        class="md:hidden inline-flex items-center justify-center gap-2 rounded-full px-4 py-2.5
               border border-lyreco-light-gray/70 bg-lyreco-white/80 text-sm font-semibold text-lyreco-blue shadow-sm
               transition hover:bg-lyreco-green/10
               focus:outline-none focus-visible:ring-2 focus-visible:ring-primary-ring focus-visible:ring-offset-2 focus-visible:ring-offset-surface"
        data-nav-toggle
        aria-controls="mobile-nav"
        aria-expanded="false"
      >
        <span class="sr-only">Ouvrir le menu</span>
        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
          <path stroke-linecap="round" stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
        </svg>
        Menu
      </button>
    </div>

    <!-- Mobile panel -->
    <div id="mobile-nav" data-nav-panel hidden class="pb-4 md:hidden">
      <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/85 shadow-sm p-3">
        <nav class="flex flex-col gap-2" aria-label="Navigation mobile">
          <?php foreach ($navItems as $item): ?>
            <?= $renderNavLink($item, true) ?>
          <?php endforeach; ?>
        </nav>
      </div>
    </div>

  </div>
</header>

<main id="content" class="flex-1 mx-auto max-w-7xl w-full px-4 sm:px-6 lg:px-8 py-8">