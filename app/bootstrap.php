<?php
const ERROR_PRESENTER_LINK="Error";
require __DIR__ . '/../vendor/autoload.php';
const APP_DIR = __DIR__ . '/';

$configurator = new \Nette\Configurator();

$configurator->setDebugMode(true);
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();


$parameters = \Nette\Utils\Finder::findFiles('*.neon')->in(__DIR__ . '/config')->limitDepth(5);
foreach ($parameters as $key => $file) {
    $configurator->addConfig($key);
}

$container = $configurator->createContainer();
return $container;
