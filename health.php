<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

use Design\Auth\AuthMode;
use Design\Kernel\KernelFactory;
use Design\Session\Exception\SessionException;


$checks = [];
$add = static function (string $name, bool $ok, string $detail = '') use (&$checks): void {
    $checks[] = [$name, $ok, $detail];
};

try {
    $kernel = KernelFactory::createForHealth($_SERVER);

    $add('Settings', true, 'OK');
    $add('Logger', true, 'OK');
    $add('SessionManager', true, 'OK');

    // IMPORTANT: start session BEFORE any output
    $kernel->session()->start();
    $add('Session start', $kernel->session()->isStarted(), $kernel->session()->isStarted() ? 'OK' : 'KO');

    $session = $kernel->session();

    // Basic session operations
    $session->set('health_key', 'hello');
    $add('Session set/get', $session->get('health_key') === 'hello', 'get(health_key) === "hello"');

    $session->remove('health_key');
    $add('Session remove', $session->get('health_key', null) === null, 'removed => default null');

    $session->set('a', 1);
    $session->set('b', 2);
    $session->clear();
    $add('Session clear', $session->all() === [], 'all() is empty');

    // Flash (separate module)
    $flash = $kernel->flash();
    $flash->set('success', 'Welcome!');
    $add('Flash set/consume', $flash->consume('success') === 'Welcome!', 'consumed once');
    $add('Flash consume default', $flash->consume('success', 'none') === 'none', 'default returned');

    $flash->set('x', 'y');
    $flash->clear();
    $add('Flash clear', $flash->consume('x', 'missing') === 'missing', 'cleared => missing');

// CSRF
$csrf = $kernel->csrf();
$token = $csrf->getToken();
$add('CSRF token generated', $token !== '', 'token generated');

$add(
    'CSRF validation',
    $csrf->validateAndRegenerate($token),
    'validateAndRegenerate OK'
);

$add(
    'CSRF invalid token',
    !$csrf->isValid('invalid'),
    'invalid rejected'
);


$auth = $kernel->auth();
$user = $auth->user();

$add('Auth role', true, $auth->role()->value);
$add('Auth authenticated', $user->isAuthenticated(), $user->isAuthenticated() ? 'yes' : 'no');
$add('Auth user id', true, (string)($user->id() ?? 'null'));
$add('Auth user name', true, (string)($user->name() ?? 'null'));




    // Regenerate + Destroy
    $session->regenerate();
    $add('Session regenerate', true, 'OK');

    $session->destroy();
    $add('Session destroy', !$session->isStarted(), $session->isStarted() ? 'KO' : 'OK');

    $logsPath = $kernel->paths()->logsPath;
} catch (SessionException $e) {
    $add('Session', false, $e->getMessage());
    $logsPath = '(unknown)';
} catch (\Throwable $e) {
    $add('Bootstrap', false, $e->getMessage());
    $logsPath = '(unknown)';
}

header('Content-Type: text/html; charset=utf-8');

$allOk = true;
foreach ($checks as $c) {
    if ($c[1] === false) { $allOk = false; break; }
}
?>
<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Health Check</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 24px; }
        .ok { color: #0a7a0a; font-weight: bold; }
        .ko { color: #b00020; font-weight: bold; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-weight: bold; }
        .badge.ok { background: #e6ffe6; }
        .badge.ko { background: #ffe6e6; }
        table { border-collapse: collapse; width: 100%; max-width: 900px; }
        td, th { border: 1px solid #ddd; padding: 10px; }
        th { text-align: left; background: #f6f6f6; }
        code { background: #f6f6f6; padding: 2px 6px; border-radius: 6px; }
    </style>
</head>
<body>
<h1>Health Check</h1>

<p>
    Status global :
    <span class="badge <?= $allOk ? 'ok' : 'ko' ?>">
        <?= $allOk ? 'OK' : 'KO' ?>
    </span>
</p>

<table>
    <thead>
    <tr>
        <th>Check</th>
        <th>Status</th>
        <th>Détails</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($checks as [$name, $ok, $detail]): ?>
        <tr>
            <td><?= htmlspecialchars($name) ?></td>
            <td class="<?= $ok ? 'ok' : 'ko' ?>"><?= $ok ? 'OK' : 'KO' ?></td>
            <td><?= htmlspecialchars($detail) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

<p>
    Logs attendus dans : <code><?= htmlspecialchars($logsPath) ?></code>
</p>
</body>
</html>
