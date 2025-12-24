<?php declare(strict_types=1);
/** @var int $status */
/** @var string $message */
/** @var string $baseUrl */
/** @var string|null $requestId */
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title><?= htmlspecialchars((string)$status) ?> - Error</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
  <main style="max-width:720px;margin:60px auto;font-family:Arial,sans-serif;">
    <h1 style="font-size:28px;margin-bottom:10px;">
      <?= htmlspecialchars((string)$status) ?> — <?= $status === 404 ? 'Not found' : 'Error' ?>
    </h1>

    <p style="opacity:.85;">
      <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
    </p>

    <?php if (!empty($requestId)): ?>
      <p style="margin-top:12px;opacity:.7;">
        Request ID: <?= htmlspecialchars($requestId, ENT_QUOTES, 'UTF-8') ?>
      </p>
    <?php endif; ?>

    <p style="margin-top:18px;">
      <a href="<?= htmlspecialchars($baseUrl ?: '/', ENT_QUOTES, 'UTF-8') ?>">Back to home</a>
    </p>
  </main>
</body>
</html>
