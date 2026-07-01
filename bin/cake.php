#!/usr/bin/php -q
<?php
declare(strict_types=1);

use App\Application;
use Cake\Console\CommandRunner;

require dirname(__DIR__) . '/vendors/autoload.php';

$runner = new CommandRunner(new Application(dirname(__DIR__) . '/config'), 'cake');
exit($runner->run($argv));
