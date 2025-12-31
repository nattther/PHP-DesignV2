<?php
declare(strict_types=1);

/**
 * Expected vars (recommended to pass them from PhpErrorRenderer/Layout):
 * @var int $statusCode
 * @var string $publicMessage
 * @var string|null $errorId
 * @var string $baseUrl
 * @var Design\Path\AssetPaths $assets
 * @var string $appName
 * @var string $faviconRelative   e.g. "img/logo/Lyreco_Logo.ico"
 * @var string $logoRelative      e.g. "img/logo/Lyreco_Logo.webp"
 */

$status = isset($statusCode) ? (int) $statusCode : 500;

$appName = $appName ?? 'Lyreco';
$baseUrl = isset($baseUrl) ? rtrim((string) $baseUrl, '/') : '';

$faviconRelative = $faviconRelative ?? 'img/logo/Lyreco_Logo.ico';
$logoRelative    = $logoRelative ?? 'img/logo/Lyreco_Logo.webp';

$assets = $assets ?? null;

// CSS
$cssFile = $assets ? $assets->cssFilePath('output.css') : null;
$cssUrl  = $assets ? $assets->cssUrl('output.css') : ($baseUrl . '/assets/css/output.css');
$cssVer  = ($cssFile && is_file($cssFile)) ? (string) filemtime($cssFile) : '1';

// Favicon
$faviconRelative = $faviconRelative ?? 'img/logo/Lyreco_Logo.ico';
$faviconUrl = $assets ? $assets->assetUrl($faviconRelative) : ($baseUrl . '/assets/' . ltrim($faviconRelative, '/'));

// Logo (affiché dans le header)
$logoRelative = $logoRelative ?? 'img/logo/Lyreco_Logo.webp';
$logoUrl = $assets ? $assets->assetUrl($logoRelative) : ($baseUrl . '/assets/' . ltrim($logoRelative, '/'));
/**
 * Status-driven UI tokens (no gradients, just clean borders + soft tints)
 */
$ui = match ($status) {
    403 => [
        'title' => 'Accès refusé',
        'hint'  => $publicMessage !== '' ? $publicMessage : "Vous n’avez pas les droits nécessaires.",
        'badge' => 'bg-lyreco-warning-light text-lyreco-warning-dark border-lyreco-warning/20',
        'ring'  => 'ring-lyreco-warning/15',
        'icon'  => 'text-lyreco-warning-dark',
    ],
    404 => [
        'title' => 'Page introuvable',
        'hint'  => $publicMessage !== '' ? $publicMessage : "Le contenu demandé n’existe pas (ou a été déplacé).",
        'badge' => 'bg-lyreco-information-light text-lyreco-information-dark border-lyreco-information/20',
        'ring'  => 'ring-lyreco-information/15',
        'icon'  => 'text-lyreco-information-dark',
    ],
    405 => [
        'title' => 'Méthode non autorisée',
        'hint'  => $publicMessage !== '' ? $publicMessage : "Cette action n’est pas disponible de cette manière.",
        'badge' => 'bg-lyreco-information-light text-lyreco-information-dark border-lyreco-information/20',
        'ring'  => 'ring-lyreco-information/15',
        'icon'  => 'text-lyreco-information-dark',
    ],
    default => [
        'title' => 'Erreur serveur',
        'hint'  => $publicMessage !== '' ? $publicMessage : "Une erreur est survenue. Réessayez dans un instant.",
        'badge' => 'bg-lyreco-error-light text-lyreco-error-dark border-lyreco-error/20',
        'ring'  => 'ring-lyreco-error/15',
        'icon'  => 'text-lyreco-error-dark',
    ],
};

$pageTitle =   $appName . ' | '  . $ui['title'] ;

/**
 * Override your global background-image (you asked: no gradients)
 * Your base layer sets a radial-gradient on body; here we disable it.
 */
$disableBgImage = 'background-image: none;';
?>
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="stylesheet" href="<?= htmlspecialchars($cssUrl . '?v=' . $cssVer) ?>">
  <link rel="icon" href="<?= htmlspecialchars($faviconUrl) ?>" sizes="any">
</head>

<body class="font-noto-sans bg-lyreco-dark-white text-lyreco-dark min-h-screen flex flex-col">
<a class="sr-only focus:not-sr-only focus:fixed focus:top-3 focus:left-3 focus:z-[60] focus:rounded-xl focus:bg-lyreco-white focus:px-4 focus:py-2 focus:shadow"
   href="#content">
  Aller au contenu
</a>

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
            <?= htmlspecialchars($ui['title']) ?>
          </div>

        </div>
        
      </a>

        <a
          href="<?= htmlspecialchars($baseUrl ?: '/') ?>"
          class="inline-flex items-center justify-center rounded-full px-4 py-2 text-sm font-semibold
                 bg-lyreco-blue text-lyreco-white hover:bg-lyreco-blue-hover
                 focus:outline-none focus:ring-4 focus:ring-primary/20"
        >
          Accueil
        </a>
      </div>
    </div>
  </header>

  <!-- Main -->
  <main class="flex-1">
    <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8 py-40">
      <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white shadow-sm overflow-hidden">
        <!-- Top status strip (flat, no gradient) -->
        <div class="px-6 sm:px-8 py-5 border-b border-lyreco-light-gray/70 bg-lyreco-white">
          <div class="flex items-start justify-between gap-4">
            <div>
              <div class="inline-flex items-center gap-2">
                <span class="inline-flex items-center rounded-full border px-2.5 py-1 text-xs font-semibold <?= $ui['badge'] ?>">
                  HTTP <?= (int) $status ?>
                </span>
              </div>

              <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold tracking-tight text-lyreco-dark">
                <?= htmlspecialchars($ui['title']) ?>
              </h1>

              <p class="mt-2 text-sm sm:text-base text-lyreco-dark-gray leading-relaxed">
                <?= htmlspecialchars($ui['hint']) ?>
              </p>
            </div>

            <!-- Simple icon (no illustration / no gradient) -->
            <div class="hidden sm:flex items-center justify-center h-12 w-12 rounded-2xl bg-lyreco-dark-white border border-lyreco-light-gray/70 ring-4 <?= $ui['ring'] ?>">
              <svg class="h-6 w-6 <?= $ui['icon'] ?>" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <path d="M12 9v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M12 17h.01" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M10.29 3.86 2.82 17.2A2 2 0 0 0 4.56 20h14.88a2 2 0 0 0 1.74-2.8L13.71 3.86a2 2 0 0 0-3.42 0Z"
                      stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
              </svg>
            </div>
          </div>
        </div>



          <?php if (!empty($errorId)) : ?>
            <div class="mt-6 rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-dark-white p-4">
              <div class="text-xs font-semibold text-lyreco-dark-gray">Référence support</div>
              <div class="mt-1 font-mono text-sm text-lyreco-dark"><?= htmlspecialchars((string) $errorId) ?></div>
              <p class="mt-2 text-xs text-lyreco-dark-gray">
                Communique cette référence au support si le problème persiste.
              </p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </main>

  <!-- Footer (always bottom) -->
  <footer class="mt-auto border-t border-lyreco-light-gray/70 bg-lyreco-dark-white" style="<?= $disableBgImage ?>">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
      <p class="text-center text-xs sm:text-sm text-lyreco-dark-gray">
        &copy; Lyreco <?= (int) date('Y') ?> - Développement Lyreco France
      </p>
    </div>
  </footer>
</body>
</html>
