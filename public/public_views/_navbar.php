<?php
declare(strict_types=1);

/** @var Design\Auth\AuthContext $auth */
/** @var string $baseUrl */

$cssPath = __DIR__ . '/../../assets/css/output.css'; // ajuste si besoin selon ton arborescence
$cssUrl  = rtrim($baseUrl, '/') . '/assets/css/output.css';
$cssVer  = is_file($cssPath) ? (string) filemtime($cssPath) : '1';
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

<link rel="stylesheet" href="<?= htmlspecialchars($cssUrl . '?v=' . $cssVer) ?>">
</head>
<body>

<?php if ($auth->isForbidden()): ?>
    <nav class="p-4 bg-gray-100 border-b">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="<?= htmlspecialchars($baseUrl) ?>" class="font-semibold">Home</a>
            <span class="text-sm text-gray-600">Access restricted</span>
        </div>
    </nav>
    <?php return; ?>
<?php endif; ?>




<nav class="p-4 bg-white border-b">
    <div class="max-w-6xl mx-auto flex items-center justify-between">
        <a href="<?= htmlspecialchars($baseUrl) ?>" class="font-semibold text-lyreco-blue">My App</a>

        <button
            type="button"
            class="md:hidden inline-flex items-center gap-2 px-3 py-2 rounded border"
            data-burger
            aria-controls="main-menu"
            aria-expanded="false"
        >
            Menu
        </button>

        <div id="main-menu" class="hidden md:flex items-center gap-6" data-menu>
            <a class="hover:underline" href="<?= htmlspecialchars($baseUrl) ?>/">Home</a>
            <a class="hover:underline" href="<?= htmlspecialchars($baseUrl) ?>/contact">About</a>
        </div>
    </div>
</nav>
