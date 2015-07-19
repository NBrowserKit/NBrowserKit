<?php

require __DIR__ . '/../vendor/autoload.php';

Tester\Environment::setup();

if (!file_exists(__DIR__ . '/../temp/cache')) {
	mkdir(__DIR__ . '/../temp/cache', 0777, TRUE);
}

$configurator = new Nette\Configurator;
$configurator->setDebugMode(FALSE);
$configurator->setTempDirectory(__DIR__ . '/../temp');
$configurator->addConfig(__DIR__ . '/config.neon');

return $configurator->createContainer();
