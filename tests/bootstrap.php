<?php

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

if (file_exists(dirname(__DIR__) . '/config/bootstrap.php')) {
    require dirname(__DIR__) . '/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__) . '/.env');
}

$kernel = new App\Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$application = new Application($kernel);
$application->setAutoExit(false);
$output = new ConsoleOutput();

$execute = function (string $command, array $input = []) use ($application, $output): void {
    $input = new ArrayInput(array_merge($input, ['command' => $command, '--no-interaction' => true]));
    $input->setInteractive(false);
    $application->doRun($input, $output);
};

$execute('doctrine:database:drop', ['--force' => true,'--if-exists' => true]);
$execute('doctrine:database:create', []);
$execute('doctrine:migrations:migrate', []);
$execute('doctrine:fixtures:load');

$kernel->shutdown();
