<?php
declare(strict_types=1);

/** @var string $routeArea */
/** @var string $viewName */
/** @var Design\Path\AssetPaths $assets */

function scriptTagVersioned(Design\Path\AssetPaths $assets, string $relative): string
{
    $file = $assets->jsFilePath($relative);
    if (!is_file($file)) {
        return '';
    }

    $v = (string) filemtime($file);
    $url = $assets->jsUrl($relative) . '?v=' . rawurlencode($v);

    return '<script src="' . htmlspecialchars($url) . '" defer></script>';
}

$globalRel = 'public/app/index.js';
$pageRel   = "{$routeArea}/{$viewName}/index.js";
?>

</main>

<footer class="mt-auto border-t border-lyreco-light-gray/70 bg-lyreco-dark-white">
  <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
    <p class="text-center text-xs sm:text-sm text-lyreco-dark-gray">
      &copy; Lyreco <?= (int) date('Y') ?> - Développement Lyreco France
    </p>
  </div>
</footer>

<?= scriptTagVersioned($assets, $globalRel) ?>
<?= scriptTagVersioned($assets, $pageRel) ?>

</body>
</html>
