<?php
use Slim\Factory\AppFactory;
use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

// Set up settings
$settings = require __DIR__ . '/../app/settings.php';
$settings($containerBuilder);

// Build PHP-DI Container instance
$container = $containerBuilder->build();

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addErrorMiddleware($container->get('settings')['displayErrorDetails'], true, true);
$app->addBodyParsingMiddleware();

$capsule = new Illuminate\Database\Capsule\Manager;
$capsule->addConnection($container->get('settings')['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

include __DIR__ . '/../route/route.php';

$app->run();