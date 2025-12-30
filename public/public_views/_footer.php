<?php
declare(strict_types=1);

/** @var string $baseUrl */

$navbarJs = rtrim($baseUrl, '/') . '/assets/js/public/navbar/index.js';
?>

<script src="<?= htmlspecialchars($navbarJs) ?>" defer></script>
</body>
</html>
