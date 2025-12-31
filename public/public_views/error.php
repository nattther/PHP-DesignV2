<?php
declare(strict_types=1);

/**
 * Variables attendues (avec fallback si non fournies):
 * @var int|null $statusCode
 * @var string|null $publicMessage
 * @var string|null $errorId           (optionnel, utile pour support)
 * @var string|null $baseUrl
 * @var Design\Path\AssetPaths|null $assets
 * @var string|null $appName
 * @var string|null $faviconRelative   ex: "img/logo/Lyreco_Logo.ico" OU "logo/Lyreco_Logo.ico" selon ta convention
 * @var string|null $logoRelative      ex: "img/logo/Lyreco_Logo.webp"
 */

$status = isset($statusCode) ? (int) $statusCode : 500;

$appName = $appName ?? 'Lyreco';
$baseUrl = isset($baseUrl) ? rtrim((string) $baseUrl, '/') : '';

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

// Titre / textes “safe” (évite fuite d’info en prod) :contentReference[oaicite:2]{index=2}
$labels = [
  403 => ['title' => 'Accès refusé', 'hint' => "Vous n’avez pas les droits nécessaires."],
  404 => ['title' => 'Page introuvable', 'hint' => "Le contenu demandé n’existe pas (ou a été déplacé)."],
  405 => ['title' => 'Méthode non autorisée', 'hint' => "Cette action n’est pas disponible de cette manière."],
  500 => ['title' => 'Erreur serveur', 'hint' => "Une erreur est survenue. Réessayez dans un instant."],
];

$title = $labels[$status]['title'] ?? 'Erreur';
$hint  = $labels[$status]['hint'] ?? 'Une erreur est survenue.';

$publicMessage = (isset($publicMessage) && is_string($publicMessage) && $publicMessage !== '')
  ? $publicMessage
  : $hint;

$pageTitle = "{$title} • {$appName}";
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

<header class="sticky top-0 z-50 border-b border-lyreco-light-gray/70 bg-lyreco-white/90 backdrop-blur">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
    <div class="h-16 flex items-center justify-between gap-4">
      <a href="<?= htmlspecialchars($baseUrl ?: '/') ?>" class="group inline-flex items-center gap-3">
        <img
          src="<?= htmlspecialchars($logoUrl) ?>"
          alt="<?= htmlspecialchars($appName) ?>"
          class="h-12 w-12 shrink-0 rounded-full bg-white object-contain ring-2 ring-lyreco-blue/15"
          loading="lazy"
        />
        <div class="leading-tight">
          <div class="flex items-baseline gap-2">
            <div class="text-base font-extrabold tracking-tight text-lyreco-blue group-hover:text-lyreco-blue-hover">
              <?= htmlspecialchars($appName) ?>
            </div>
            <span class="text-[11px] font-semibold text-lyreco-dark-gray translate-y-[1px]">by Hive</span>
          </div>
          <div class="text-xs text-lyreco-dark-gray">
            <?= htmlspecialchars($title) ?>
          </div>
        </div>
      </a>

      <a
        href="<?= htmlspecialchars($baseUrl ?: '/') ?>"
        class="rounded-full px-4 py-2 text-sm font-semibold bg-lyreco-blue text-lyreco-white hover:bg-lyreco-blue-hover"
      >
        Retour à l’accueil
      </a>
    </div>
  </div>
</header>

<main class="flex-1 mx-auto max-w-3xl w-full px-4 sm:px-6 lg:px-8 py-10">
  <div class="rounded-3xl border border-lyreco-light-gray/70 bg-lyreco-white/80 shadow-sm p-6 sm:p-8">
    <div class="flex items-start justify-between gap-4">
      <div>
        <p class="text-xs font-semibold text-lyreco-dark-gray">HTTP <?= (int) $status ?></p>
        <h1 class="mt-1 text-2xl sm:text-3xl font-extrabold tracking-tight text-lyreco-dark">
          <?= htmlspecialchars($title) ?>
        </h1>
        <p class="mt-3 text-sm sm:text-base text-lyreco-dark-gray">
          <?= htmlspecialchars($publicMessage) ?>
        </p>
      </div>

      <div class="hidden sm:block rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-dark-white px-4 py-3">
        <div class="text-xs font-semibold text-lyreco-dark-gray">Astuce</div>
        <div class="mt-1 text-sm text-lyreco-dark">
          Vérifie l’URL ou reviens à l’accueil.
        </div>
      </div>
    </div>

    <div class="mt-6 flex flex-wrap gap-2">
      <button
        type="button"
        class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10"
        onclick="history.back()"
      >
        Page précédente
      </button>

      <a
        href="<?= htmlspecialchars(($baseUrl ?: '') . '/contact') ?>"
        class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10"
      >
        Contact
      </a>

      <a
        href="<?= htmlspecialchars($baseUrl ?: '/') ?>"
        class="rounded-full px-4 py-2 text-sm font-semibold border border-lyreco-light-gray/70 bg-lyreco-white/80 hover:bg-lyreco-green/10"
      >
        Accueil
      </a>
    </div>

    <?php if (!empty($errorId)) : ?>
      <div class="mt-6 rounded-2xl border border-lyreco-light-gray/70 bg-lyreco-dark-white p-4">
        <div class="text-xs font-semibold text-lyreco-dark-gray">Référence support</div>
        <div class="mt-1 font-mono text-sm text-lyreco-dark"><?= htmlspecialchars((string) $errorId) ?></div>
      </div>
    <?php endif; ?>
  </div>
</main>

<footer class="mt-auto border-t border-lyreco-light-gray/70 bg-lyreco-dark-white">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <p class="text-center text-xs sm:text-sm text-lyreco-dark-gray">
      &copy; Lyreco <?= (int) date('Y') ?> - Développement Lyreco France
    </p>
  </div>
</footer>

</body>
</html>
