<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Kernel\KernelFactory;

$kernel = KernelFactory::createForFront($_SERVER);

$kernel->session()->start();
$kernel->logger()->info('Front started');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$kernel->csrf()->validateAndRegenerate($_POST['_csrf'] ?? '')) {
        http_response_code(400);
        echo 'Invalid CSRF token';
        exit;
    }

    $kernel->flash()->set('success', 'Form submitted!');
    header('Location: /');
    exit;
}

$flash = $kernel->flash()->consume('success');
$csrfToken = $kernel->csrf()->getToken();
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Front</title>
</head>
<body>

<?php if ($flash): ?>
    <p style="color: green"><?= htmlspecialchars($flash) ?></p>
<?php endif; ?>

<form method="post">
    <input type="hidden" name="_csrf" value="<?= htmlspecialchars($csrfToken) ?>">
    <button type="submit">Submit</button>
</form>

</body>
</html>
