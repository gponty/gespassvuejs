<?php

/*
 * g.ponty@dev-web.io
 */

declare(strict_types=1);

require_once dirname(__DIR__).'/config/bootstrap.php';

$kernel = new \App\Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? true));

$kernel->boot();

return $kernel->getContainer()->get('doctrine')->getManager();
